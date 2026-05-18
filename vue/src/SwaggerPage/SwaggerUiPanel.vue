<!--
  Matomo - free/libre analytics platform

  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    v-if="isLoading && !spec && !displayError"
    class="swaggerLoader"
  >
    <ActivityIndicator :loading="true" />
  </div>

  <Alert
    v-if="displayError"
    severity="danger"
  >
    {{ displayError }}
  </Alert>

  <div
    :id="swaggerContainerId"
    :class="['swaggerMount', { 'swaggerMount--ready': isReady }]"
  />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ActivityIndicator, Alert, translate } from 'CoreHome';

const activeCopySuccessStateKey = '__matomoActiveCopySuccessState';
const summaryPathClickHandlerAttachedKey = '__matomoSummaryPathClickHandlerAttached';
const summaryPrefix = '/index.php?module=API&method=';
const interactiveSwaggerSelector = '.opblock-tag, .opblock-summary, .expand-operation, .opblock-summary-control';
const copyIconMarkup = '<span class="icon-content-copy" aria-hidden="true"></span>';
const copySuccessIconMarkup = '<i class="icon-ok matomo-copy-success-icon" aria-hidden="true"></i>';

interface OpenApiSpec {
  [key: string]: unknown;
}

interface SwaggerUiRequest {
  method?: string;
  url?: string;
  body?: unknown;
  headers?: Record<string, unknown>;
  [key: string]: unknown;
}

interface OpenApiServer {
  url?: string;
  [key: string]: unknown;
}

type SwaggerUiFactory = (config: {
  defaultModelsExpandDepth: number;
  deepLinking: boolean;
  docExpansion: 'list' | 'full' | 'none';
  dom_id: string;
  layout: string;
  onComplete?: () => void;
  plugins?: Array<() => unknown>;
  presets?: unknown[];
  requestInterceptor?: (request: SwaggerUiRequest) => SwaggerUiRequest;
  spec?: OpenApiSpec;
  tagsSorter?: string;
}) => unknown;

type SwaggerRootElement = HTMLElement & {
  [activeCopySuccessStateKey]?: {
    element: HTMLElement;
    resetTimeoutId: number;
  } | null;
  [summaryPathClickHandlerAttachedKey]?: boolean;
};

type SwaggerWindow = Window & {
  SwaggerUIBundle?: SwaggerUiFactory & {
    presets?: {
      apis?: unknown;
    };
  };
};

interface SwaggerUiPanelState {
  isReady: boolean;
  loadError: string | null;
}

export default defineComponent({
  components: {
    ActivityIndicator,
    Alert,
  },
  props: {
    plugin: {
      type: String as PropType<string>,
      required: true,
    },
    piwikUrl: {
      type: String as PropType<string | null>,
      default: null,
    },
    spec: {
      type: Object as PropType<OpenApiSpec | null>,
      default: null,
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    specLoadError: {
      type: String as PropType<string | null>,
      default: null,
    },
  },
  data(): SwaggerUiPanelState {
    return {
      isReady: false,
      loadError: null,
    };
  },
  computed: {
    displayError(): string | null {
      return this.specLoadError || this.loadError;
    },
    swaggerContainerId(): string {
      return `swagger-ui-${this.plugin}`;
    },
  },
  watch: {
    spec: {
      immediate: true,
      handler(spec: OpenApiSpec | null) {
        if (spec) {
          this.renderSwaggerUi();
          return;
        }

        this.resetSwaggerUi();
      },
    },
  },
  beforeUnmount() {
    const container = this.getSwaggerRoot();

    if (!container) {
      return;
    }

    this.clearCopySuccessState(container);
  },
  methods: {
    getSwaggerRoot(): SwaggerRootElement | null {
      return document.getElementById(this.swaggerContainerId) as SwaggerRootElement | null;
    },
    getSpecWithCurrentInstanceUrl(spec: OpenApiSpec): OpenApiSpec {
      if (!this.piwikUrl) {
        return spec;
      }

      return {
        ...spec,
        servers: [{ url: this.piwikUrl } as OpenApiServer],
      };
    },
    getOperationForRequest(
      spec: OpenApiSpec,
      requestUrl: string,
      requestMethod: string,
    ): Record<string, unknown> | null {
      const operationId = new URL(requestUrl, window.location.href).searchParams.get('method');
      const { paths } = spec;

      if (!operationId || !paths || typeof paths !== 'object') {
        return null;
      }

      const methodName = requestMethod.toLowerCase();
      return Object
        .values(paths as Record<string, unknown>)
        .reduce<Record<string, unknown> | null>((found, pathItem) => {
          if (found || !pathItem || typeof pathItem !== 'object') {
            return found;
          }

          const operation = (pathItem as Record<string, unknown>)[methodName];
          if (!operation || typeof operation !== 'object') {
            return found;
          }

          return (operation as Record<string, unknown>).operationId === operationId
            ? operation as Record<string, unknown>
            : found;
        }, null);
    },
    resolveParameterSpec(spec: OpenApiSpec, parameter: unknown): Record<string, unknown> | null {
      if (!parameter || typeof parameter !== 'object') {
        return null;
      }

      const ref = (parameter as Record<string, unknown>).$ref;
      if (typeof ref !== 'string' || !ref.startsWith('#/components/parameters/')) {
        return parameter as Record<string, unknown>;
      }

      const componentName = ref.substring('#/components/parameters/'.length);
      const components = spec.components as Record<string, unknown> | undefined;
      const { parameters } = components || {};
      if (!parameters || typeof parameters !== 'object') {
        return null;
      }

      const resolved = (parameters as Record<string, unknown>)[componentName];
      return resolved && typeof resolved === 'object' ? resolved as Record<string, unknown> : null;
    },
    resolveSchemaSpec(spec: OpenApiSpec, schema: unknown): Record<string, unknown> | null {
      if (!schema || typeof schema !== 'object') {
        return null;
      }

      const ref = (schema as Record<string, unknown>).$ref;
      if (typeof ref !== 'string' || !ref.startsWith('#/components/schemas/')) {
        return schema as Record<string, unknown>;
      }

      const componentName = ref.substring('#/components/schemas/'.length);
      const components = spec.components as Record<string, unknown> | undefined;
      const { schemas } = components || {};
      if (!schemas || typeof schemas !== 'object') {
        return null;
      }

      const resolved = (schemas as Record<string, unknown>)[componentName];
      return resolved && typeof resolved === 'object' ? resolved as Record<string, unknown> : null;
    },
    isArrayQueryParameter(parameter: Record<string, unknown>): boolean {
      if (parameter.in !== 'query') {
        return false;
      }

      const { schema } = parameter;
      if (!schema || typeof schema !== 'object') {
        return false;
      }

      const schemaObject = schema as Record<string, unknown>;
      if (schemaObject.type === 'array') {
        return true;
      }

      const { oneOf } = schemaObject;
      if (!Array.isArray(oneOf)) {
        return false;
      }

      return oneOf.some(
        (branch) => branch && typeof branch === 'object' && (branch as Record<string, unknown>).type === 'array',
      );
    },
    getArrayQueryParameterNamesForRequest(
      spec: OpenApiSpec,
      requestUrl: string,
      requestMethod: string,
    ): string[] {
      const operation = this.getOperationForRequest(spec, requestUrl, requestMethod);
      const parameters = operation?.parameters;

      if (!Array.isArray(parameters)) {
        return [];
      }

      return parameters
        .map((parameter) => this.resolveParameterSpec(spec, parameter))
        .filter((parameter): parameter is Record<string, unknown> => Boolean(parameter))
        .filter((parameter) => this.isArrayQueryParameter(parameter))
        .map((parameter) => parameter.name)
        .filter((name): name is string => typeof name === 'string' && name.length > 0);
    },
    rewriteMatomoArrayQueryParams(request: SwaggerUiRequest, spec: OpenApiSpec): SwaggerUiRequest {
      if (!request.url) {
        return request;
      }

      const arrayQueryParameterNames = this.getArrayQueryParameterNamesForRequest(spec, request.url, request.method || 'get');
      if (!arrayQueryParameterNames.length) {
        return request;
      }

      const parsedUrl = new URL(request.url, window.location.href);
      let didRewriteUrl = false;

      arrayQueryParameterNames.forEach((parameterName) => {
        const values = parsedUrl.searchParams.getAll(parameterName);
        if (!values.length) {
          return;
        }

        parsedUrl.searchParams.delete(parameterName);
        values.forEach((value, index) => {
          parsedUrl.searchParams.append(`${parameterName}[${index}]`, value);
        });
        didRewriteUrl = true;
      });

      if (didRewriteUrl) {
        request.url = parsedUrl.toString();
      }

      return request;
    },
    resolveFormRequestBodySchema(
      spec: OpenApiSpec,
      requestUrl: string,
      requestMethod: string,
    ): Record<string, unknown> | null {
      const operation = this.getOperationForRequest(spec, requestUrl, requestMethod);
      const requestBody = operation?.requestBody;
      if (!requestBody || typeof requestBody !== 'object') {
        return null;
      }

      const { content } = requestBody as Record<string, unknown>;
      if (!content || typeof content !== 'object') {
        return null;
      }

      const formContent = (content as Record<string, unknown>)['application/x-www-form-urlencoded'];
      if (!formContent || typeof formContent !== 'object') {
        return null;
      }

      return this.resolveSchemaSpec(spec, (formContent as Record<string, unknown>).schema);
    },
    isObjectLikeFormSchema(spec: OpenApiSpec, schema: Record<string, unknown>): boolean {
      if (schema.type === 'object') {
        return true;
      }

      if (schema.type !== 'array') {
        return false;
      }

      const items = this.resolveSchemaSpec(spec, schema.items);
      return Boolean(items?.type === 'object');
    },
    getObjectFormSchemasForRequest(
      spec: OpenApiSpec,
      requestUrl: string,
      requestMethod: string,
    ): Record<string, Record<string, unknown>> {
      const schema = this.resolveFormRequestBodySchema(spec, requestUrl, requestMethod);
      const properties = schema?.properties;

      if (!properties || typeof properties !== 'object') {
        return {};
      }

      return Object.entries(properties as Record<string, unknown>)
        .reduce<Record<string, Record<string, unknown>>>((result, [name, propertySchema]) => {
          const resolved = this.resolveSchemaSpec(spec, propertySchema);
          if (resolved && this.isObjectLikeFormSchema(spec, resolved)) {
            result[name] = resolved;
          }

          return result;
        }, {});
    },
    getRequestContentType(request: SwaggerUiRequest): string {
      const { headers } = request;
      if (!headers || typeof headers !== 'object') {
        return '';
      }

      const match = Object.entries(headers).find(
        ([headerName]) => headerName.toLowerCase() === 'content-type',
      );

      return typeof match?.[1] === 'string' ? match[1] : '';
    },
    appendPhpFormEntries(
      target: URLSearchParams,
      keyPrefix: string,
      value: unknown,
    ) {
      if (Array.isArray(value)) {
        value.forEach((item, index) => {
          this.appendPhpFormEntries(target, `${keyPrefix}[${index}]`, item);
        });
        return;
      }

      if (value && typeof value === 'object') {
        Object.entries(value as Record<string, unknown>).forEach(([key, nestedValue]) => {
          this.appendPhpFormEntries(target, `${keyPrefix}[${key}]`, nestedValue);
        });
        return;
      }

      target.append(keyPrefix, value == null ? '' : String(value));
    },
    parseObjectLikeFormValue(
      rawValue: string,
      schema: Record<string, unknown>,
    ): unknown | null {
      const normaliseArrayValue = (parsedValue: unknown): unknown => {
        if (schema.type === 'array' && parsedValue && !Array.isArray(parsedValue) && typeof parsedValue === 'object') {
          return [parsedValue];
        }

        return parsedValue;
      };
      const tryParseJson = (jsonValue: string): unknown | null => {
        try {
          return normaliseArrayValue(JSON.parse(jsonValue) as unknown);
        } catch (error) {
          return null;
        }
      };
      const tryParseJsonLikeObject = (): unknown | null => {
        const trimmedValue = rawValue.trim();
        if (!trimmedValue.startsWith('{') || !trimmedValue.endsWith('}')) {
          return null;
        }

        const withInsertedCommas = trimmedValue.replace(
          /((?:"(?:\\.|[^"])*")|true|false|null|-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?|\}|\])(\s*")/g,
          '$1,$2',
        );

        if (schema.type === 'array') {
          return tryParseJson(`[${withInsertedCommas}]`);
        }

        return tryParseJson(withInsertedCommas);
      };

      const parsedJson = tryParseJson(rawValue);
      if (parsedJson !== null) {
        return parsedJson;
      }

      const parsedJsonLikeObject = tryParseJsonLikeObject();
      if (parsedJsonLikeObject !== null) {
        return parsedJsonLikeObject;
      }

      if (schema.type !== 'array') {
        return null;
      }

      const trimmedValue = rawValue.trim();
      if (!trimmedValue.startsWith('{') || !trimmedValue.endsWith('}')) {
        return null;
      }

      return tryParseJson(`[${trimmedValue}]`);
    },
    rewriteMatomoObjectFormParams(request: SwaggerUiRequest, spec: OpenApiSpec): SwaggerUiRequest {
      if (!request.url) {
        return request;
      }

      const contentType = this.getRequestContentType(request);
      if (!contentType.includes('application/x-www-form-urlencoded')) {
        return request;
      }

      const objectFormSchemas = this.getObjectFormSchemasForRequest(
        spec,
        request.url,
        request.method || 'get',
      );
      const objectFormParameterNames = Object.keys(objectFormSchemas);
      if (!objectFormParameterNames.length) {
        return request;
      }

      let rawBody = '';
      if (request.body instanceof URLSearchParams) {
        rawBody = request.body.toString();
      } else if (typeof request.body === 'string') {
        rawBody = request.body;
      }

      if (!rawBody) {
        return request;
      }

      const parsedBody = new URLSearchParams(rawBody);
      const rewrittenBody = new URLSearchParams();
      let didRewriteBody = false;

      parsedBody.forEach((value, key) => {
        if (!objectFormParameterNames.includes(key)) {
          rewrittenBody.append(key, value);
          return;
        }

        const schema = objectFormSchemas[key];
        const parsedValue = this.parseObjectLikeFormValue(value, schema);
        if (!parsedValue || typeof parsedValue !== 'object') {
          rewrittenBody.append(key, value);
          return;
        }

        this.appendPhpFormEntries(rewrittenBody, key, parsedValue);
        didRewriteBody = true;
      });

      if (didRewriteBody) {
        request.body = rewrittenBody.toString();
      }

      return request;
    },
    shortenSummaryPaths(swaggerRoot: ParentNode) {
      const summaryPaths = swaggerRoot.querySelectorAll<HTMLElement>('.opblock-summary-path');

      summaryPaths.forEach((element) => {
        const fullPath = element.getAttribute('data-path');

        if (!fullPath || !fullPath.startsWith(summaryPrefix)) {
          return;
        }

        element.textContent = fullPath.substring(summaryPrefix.length);
        element.setAttribute('title', fullPath);
      });
    },
    updateFlatSingleTag(swaggerRoot: ParentNode) {
      const tagSections = swaggerRoot.querySelectorAll<HTMLElement>('.opblock-tag-section');

      tagSections.forEach((tagSection) => {
        tagSection.classList.remove('matomo-flat-tag');
      });

      if (tagSections.length !== 1) {
        return;
      }

      const tagSection = tagSections[0];
      if (tagSection.querySelector(':scope > .opblock-tag')) {
        tagSection.classList.add('matomo-flat-tag');
      }
    },
    applyMatomoCopyIcons(swaggerRoot: ParentNode) {
      const copyControls = swaggerRoot.querySelectorAll<HTMLElement>('.opblock-summary .view-line-link.copy-to-clipboard');

      copyControls.forEach((element) => {
        if (element.classList.contains('matomo-copy-success')) {
          return;
        }

        element.innerHTML = copyIconMarkup;
      });
    },
    normalizeSwaggerUi(swaggerRoot: ParentNode) {
      this.shortenSummaryPaths(swaggerRoot);
      this.updateFlatSingleTag(swaggerRoot);
      this.applyMatomoCopyIcons(swaggerRoot);
    },
    getSummaryPathCopyControl(target: Element | null) {
      return target?.closest('.opblock-summary .view-line-link.copy-to-clipboard') as HTMLElement | null;
    },
    getFlatTagHeader(target: Element | null) {
      return target?.closest('.opblock-tag-section.matomo-flat-tag > .opblock-tag') as HTMLElement | null;
    },
    clearCopySuccessState(swaggerRoot: SwaggerRootElement) {
      const state = swaggerRoot[activeCopySuccessStateKey];

      if (!state) {
        return;
      }

      const { element } = state;

      window.clearTimeout(state.resetTimeoutId);
      element.innerHTML = copyIconMarkup;
      element.classList.remove('matomo-copy-success');
      element.classList.remove('matomo-copy-reset');
      window.requestAnimationFrame(() => {
        element.classList.add('matomo-copy-reset');
        window.setTimeout(() => {
          element.classList.remove('matomo-copy-reset');
        }, 300);
      });
      swaggerRoot[activeCopySuccessStateKey] = null;
    },
    showCopySuccessState(
      swaggerRoot: SwaggerRootElement,
      control: HTMLElement,
    ) {
      this.clearCopySuccessState(swaggerRoot);

      control.innerHTML = copySuccessIconMarkup;
      control.classList.remove('matomo-copy-reset');
      control.classList.add('matomo-copy-success');

      swaggerRoot[activeCopySuccessStateKey] = {
        element: control,
        resetTimeoutId: window.setTimeout(() => {
          if (swaggerRoot[activeCopySuccessStateKey]?.element === control) {
            this.clearCopySuccessState(swaggerRoot);
          }
        }, 3000),
      };
    },
    attachSwaggerInteractionHandlers(swaggerRoot: SwaggerRootElement | null) {
      if (!swaggerRoot || swaggerRoot[summaryPathClickHandlerAttachedKey]) {
        return;
      }

      swaggerRoot[summaryPathClickHandlerAttachedKey] = true;
      swaggerRoot.addEventListener('click', (event) => {
        const target = event.target as Element | null;
        const flatTagHeader = this.getFlatTagHeader(target);

        if (flatTagHeader) {
          if (!target?.closest('a')) {
            event.preventDefault();
          }

          event.stopPropagation();

          return;
        }

        const summaryPathCopyControl = this.getSummaryPathCopyControl(target);

        if (summaryPathCopyControl) {
          window.setTimeout(() => {
            if (summaryPathCopyControl.isConnected) {
              this.showCopySuccessState(swaggerRoot, summaryPathCopyControl);
            }
          }, 0);
        }

        if (!target?.closest(interactiveSwaggerSelector)) {
          return;
        }

        window.setTimeout(() => {
          this.normalizeSwaggerUi(swaggerRoot);
        }, 0);
      }, true);
    },
    resetSwaggerUi() {
      const container = this.getSwaggerRoot();

      this.isReady = false;
      this.loadError = null;

      if (!container) {
        return;
      }

      this.clearCopySuccessState(container);
      container.innerHTML = '';
    },
    renderSwaggerUi() {
      const swaggerUiBundle = (window as SwaggerWindow).SwaggerUIBundle;
      const container = this.getSwaggerRoot();

      this.isReady = false;
      this.loadError = null;

      if (!swaggerUiBundle || !container || !this.spec) {
        if (!this.spec) {
          return;
        }

        this.isReady = true;
        this.loadError = translate('OpenApiDocs_SwaggerPageSpecLoadFailed');
        return;
      }

      container.innerHTML = '';
      const specWithCurrentInstanceUrl = this.getSpecWithCurrentInstanceUrl(this.spec);

      swaggerUiBundle({
        dom_id: `#${this.swaggerContainerId}`,
        spec: specWithCurrentInstanceUrl,
        deepLinking: false,
        docExpansion: 'list',
        defaultModelsExpandDepth: -1,
        layout: 'BaseLayout',
        tagsSorter: 'alpha',
        presets: swaggerUiBundle.presets?.apis ? [swaggerUiBundle.presets.apis] : [],
        requestInterceptor: (request) => this.rewriteMatomoObjectFormParams(
          this.rewriteMatomoArrayQueryParams(
            request,
            specWithCurrentInstanceUrl,
          ),
          specWithCurrentInstanceUrl,
        ),
        onComplete: () => {
          window.setTimeout(() => {
            this.normalizeSwaggerUi(container);
            this.isReady = true;
          }, 0);

          this.attachSwaggerInteractionHandlers(container);
        },
      });
    },
  },
});
</script>

<style scoped>
.swaggerLoader {
  max-height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.swaggerMount {
  min-height: 180px;
  visibility: hidden;
}

.swaggerMount--ready {
  visibility: visible;
}

.swaggerMount :deep(.swagger-ui) {
  border: 0;
  border-radius: 0;
  color: var(--theme-color-text, #3b4151);
  font-size: 14px;
  line-height: 1.5;
  padding-top: 0;
}
</style>
