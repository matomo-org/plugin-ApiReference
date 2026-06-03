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
    <ActivityIndicator :loading="true"/>
  </div>

  <Alert
    v-if="displayError"
    severity="danger"
  >
    {{ displayError }}
  </Alert>

  <Alert
    v-else-if="!isLoading && !spec"
    severity="warning"
  >
    <span v-html="$sanitize(missingSpecLearnMore)"/>
  </Alert>

  <div
    :id="swaggerContainerId"
    :class="['swaggerMount', { 'swaggerMount--ready': isReady }]"
  />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  ActivityIndicator,
  Alert,
  externalLink,
  translate,
} from 'CoreHome';

const activeCopySuccessStateKey = '__matomoActiveCopySuccessState';
const authAutocompleteObserverKey = '__matomoSwaggerAuthAutocompleteObserver';
const summaryPathClickHandlerAttachedKey = '__matomoSummaryPathClickHandlerAttached';
const summaryPrefix = '/index.php?module=API&method=';
const interactiveSwaggerSelector = '.opblock-tag, .opblock-summary, .expand-operation, .opblock-summary-control';
const authInteractionSelector = '.scheme-container .authorize, .dialog-ux .modal-ux button';
const copyIconMarkup = '<span class="icon-content-copy" aria-hidden="true"></span>';
const copySuccessIconMarkup = '<i class="icon-ok matomo-copy-success-icon" aria-hidden="true"></i>';
const connectTokenTranslationKey = 'ApiReference_SwaggerPageConnectToken';
const removeTokenTranslationKey = 'ApiReference_SwaggerPageRemoveToken';
const tokenConnectedTranslationKey = 'ApiReference_SwaggerPageTokenConnected';
const tokenConnectedHeadingTranslationKey = 'ApiReference_SwaggerPageTokenConnectedHeading';

interface OpenApiSpec {
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
  spec?: OpenApiSpec;
  tagsSorter?: string;
}) => unknown;

type SwaggerRootElement = HTMLElement & {
  [activeCopySuccessStateKey]?: {
    element: HTMLElement;
    resetTimeoutId: number;
  } | null;
  [authAutocompleteObserverKey]?: MutationObserver | null;
  [summaryPathClickHandlerAttachedKey]?: boolean;
};

type ObjectWithOptionalHasOwn = ObjectConstructor & {
  hasOwn?: (object: Record<string, unknown>, property: PropertyKey) => boolean;
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
    missingSpecLearnMore(): string {
      return translate(
        'ApiReference_SwaggerPageSpecNotAvailable',
        externalLink('https://matomo.org/faq/how-to/how-to-use-the-api-reference-in-matomo#why-is-the-openapi-specification-file-not-generated'),
        '</a>',
      );
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

    this.clearSwaggerAuthAutocompleteObserver(container);
    this.clearCopySuccessState(container);
  },
  methods: {
    ensureObjectHasOwnSupport() {
      const objectWithHasOwn = Object as ObjectWithOptionalHasOwn;

      if (typeof objectWithHasOwn.hasOwn === 'function') {
        return;
      }

      objectWithHasOwn.hasOwn = (object: Record<string, unknown>, property: PropertyKey) => (
        Object.prototype.hasOwnProperty.call(object, property)
      );
    },
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
    suppressSwaggerAuthAutocomplete(swaggerRoot: ParentNode) {
      const authInputs = swaggerRoot.querySelectorAll<HTMLInputElement>('.modal-ux .auth-container input');

      authInputs.forEach((input) => {
        input.setAttribute('autocomplete', 'new-password');
        input.setAttribute('autocapitalize', 'off');
        input.setAttribute('spellcheck', 'false');
      });
    },
    getSwaggerAuthTextReplacements() {
      return [
        { from: 'Authorized', to: translate(tokenConnectedTranslationKey) },
        { from: 'Authorised', to: translate(tokenConnectedTranslationKey) },
        { from: 'Logout', to: translate(removeTokenTranslationKey) },
      ];
    },
    replaceSwaggerText(swaggerRoot: ParentNode) {
      const swaggerTextReplacements = this.getSwaggerAuthTextReplacements();
      const walker = document.createTreeWalker(
        swaggerRoot as unknown as Node,
        NodeFilter.SHOW_TEXT,
      );
      let currentNode = walker.nextNode();

      while (currentNode) {
        const text = currentNode.textContent?.trim();

        if (text) {
          const replacement = swaggerTextReplacements.find(({ from }) => text === from);

          if (replacement) {
            currentNode.textContent = replacement.to;
          }
        }

        currentNode = walker.nextNode();
      }
    },
    updateSwaggerAuthCopy(swaggerRoot: ParentNode) {
      this.replaceSwaggerText(swaggerRoot);

      const modal = document.querySelector('.dialog-ux .modal-ux');
      const authButton = swaggerRoot.querySelector<HTMLElement>('.scheme-container .authorize');
      const authStatus = authButton?.querySelector<HTMLElement>('span');
      const isTokenConnected = !!authButton?.classList.contains('locked');

      if (authButton) {
        authButton.classList.toggle('matomo-token-connected', isTokenConnected);
      }

      if (authStatus) {
        authStatus.textContent = isTokenConnected
          ? translate(tokenConnectedTranslationKey)
          : translate(connectTokenTranslationKey);
      }

      if (!modal) {
        return;
      }

      const modalHeading = modal.querySelector<HTMLElement>('h3');

      if (modalHeading) {
        modalHeading.textContent = isTokenConnected
          ? translate(tokenConnectedHeadingTranslationKey)
          : translate(connectTokenTranslationKey);
      }

      const authContainers = modal.querySelectorAll<HTMLElement>('.auth-container');

      authContainers.forEach((authContainer) => {
        const authButtons = authContainer.querySelectorAll<HTMLButtonElement>('button');

        authButtons.forEach((button) => {
          const buttonText = button.textContent?.trim();

          if (buttonText === 'Logout') {
            button.textContent = translate(removeTokenTranslationKey);
          }
        });
      });
    },
    attachSwaggerAuthAutocompleteObserver(swaggerRoot: SwaggerRootElement | null) {
      if (!swaggerRoot) {
        return;
      }

      this.suppressSwaggerAuthAutocomplete(document);
      this.updateSwaggerAuthCopy(swaggerRoot);

      if (swaggerRoot[authAutocompleteObserverKey] || typeof MutationObserver === 'undefined') {
        return;
      }

      const observer = new MutationObserver(() => {
        this.suppressSwaggerAuthAutocomplete(swaggerRoot);
      });

      observer.observe(swaggerRoot, {
        childList: true,
        subtree: true,
      });

      swaggerRoot[authAutocompleteObserverKey] = observer;
    },
    normalizeSwaggerUi(swaggerRoot: ParentNode) {
      this.shortenSummaryPaths(swaggerRoot);
      this.updateFlatSingleTag(swaggerRoot);
      this.applyMatomoCopyIcons(swaggerRoot);
      this.updateSwaggerAuthCopy(swaggerRoot);
    },
    getSummaryPathCopyControl(target: Element | null) {
      return target?.closest('.opblock-summary .view-line-link.copy-to-clipboard') as HTMLElement | null;
    },
    getFlatTagHeader(target: Element | null) {
      return target?.closest('.opblock-tag-section.matomo-flat-tag > .opblock-tag') as HTMLElement | null;
    },
    clearSwaggerAuthAutocompleteObserver(swaggerRoot: SwaggerRootElement) {
      const observer = swaggerRoot[authAutocompleteObserverKey];

      if (!observer) {
        return;
      }

      observer.disconnect();
      swaggerRoot[authAutocompleteObserverKey] = null;
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
        if (target?.closest(authInteractionSelector)) {
          window.setTimeout(() => {
            this.updateSwaggerAuthCopy(swaggerRoot);
            this.suppressSwaggerAuthAutocomplete(document);
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

      this.clearSwaggerAuthAutocompleteObserver(container);
      this.clearCopySuccessState(container);
      container.innerHTML = '';
    },
    renderSwaggerUi() {
      this.ensureObjectHasOwnSupport();

      const swaggerUiBundle = (window as SwaggerWindow).SwaggerUIBundle;
      const container = this.getSwaggerRoot();

      this.isReady = false;
      this.loadError = null;

      if (!swaggerUiBundle || !container || !this.spec) {
        if (!this.spec) {
          return;
        }

        this.isReady = true;
        this.loadError = translate('ApiReference_SwaggerPageSpecLoadFailed');
        return;
      }

      container.innerHTML = '';

      swaggerUiBundle({
        dom_id: `#${this.swaggerContainerId}`,
        spec: this.getSpecWithCurrentInstanceUrl(this.spec),
        deepLinking: false,
        docExpansion: 'list',
        defaultModelsExpandDepth: -1,
        layout: 'BaseLayout',
        tagsSorter: 'alpha',
        presets: swaggerUiBundle.presets?.apis ? [swaggerUiBundle.presets.apis] : [],
        onComplete: () => {
          window.setTimeout(() => {
            this.normalizeSwaggerUi(container);
            this.attachSwaggerAuthAutocompleteObserver(container);
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

.swaggerMount :deep(.swagger-ui input[disabled]),
.swaggerMount :deep(.swagger-ui select[disabled]),
.swaggerMount :deep(.swagger-ui textarea[disabled]) {
  background: var(--theme-color-background-tint, #f2f4f7);
  border-color: var(--theme-color-border-subtle, #c5ced8);
  border-style: dashed;
  color: var(--theme-color-text-lighter, #98a2b3);
  cursor: not-allowed;
  opacity: 1;
}
</style>
