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

interface OpenApiSpec {
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
    originalInnerHTML: string;
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
  mounted() {
    if (this.spec) {
      this.renderSwaggerUi();
    }
  },
  watch: {
    spec(spec: OpenApiSpec | null) {
      if (spec) {
        this.renderSwaggerUi();
        return;
      }

      this.resetSwaggerUi();
    },
  },
  beforeUnmount() {
    const container = document.getElementById(
      this.swaggerContainerId,
    ) as SwaggerRootElement | null;

    if (!container) {
      return;
    }

    this.clearCopySuccessState(container);
  },
  methods: {
    shortenSummaryPaths(swaggerRoot: ParentNode) {
      const summaryPrefix = '/index.php?module=API&method=';
      const summaryPaths = swaggerRoot.querySelectorAll('.opblock-summary-path');

      Array.prototype.forEach.call(summaryPaths, (element: Element) => {
        const fullPath = element.getAttribute('data-path');

        if (!fullPath || !fullPath.startsWith(summaryPrefix)) {
          return;
        }

        element.textContent = fullPath.substring(summaryPrefix.length);
        element.setAttribute('title', fullPath);
      });
    },
    updateFlatSingleTag(swaggerRoot: ParentNode) {
      const tagSections = swaggerRoot.querySelectorAll('.opblock-tag-section');

      Array.prototype.forEach.call(tagSections, (tagSection: Element) => {
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
      const copyControls = swaggerRoot.querySelectorAll('.opblock-summary .view-line-link.copy-to-clipboard');

      Array.prototype.forEach.call(copyControls, (element: HTMLElement) => {
        if (element.classList.contains('matomo-copy-success')) {
          return;
        }

        element.innerHTML = '<span class="icon-content-copy" aria-hidden="true"></span>';
      });
    },
    normalizeSwaggerUi(swaggerRoot: ParentNode | null) {
      if (!swaggerRoot) {
        return;
      }

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
    getOriginalCopyControlMarkup(swaggerRoot: SwaggerRootElement, control: HTMLElement) {
      const state = swaggerRoot[activeCopySuccessStateKey];

      if (state && state.element === control) {
        return state.originalInnerHTML;
      }

      return control.innerHTML;
    },
    clearCopySuccessState(swaggerRoot: SwaggerRootElement) {
      const state = swaggerRoot[activeCopySuccessStateKey];

      if (!state) {
        return;
      }

      const { element } = state;

      window.clearTimeout(state.resetTimeoutId);
      element.innerHTML = state.originalInnerHTML;
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
      originalInnerHTML: string,
    ) {
      this.clearCopySuccessState(swaggerRoot);

      control.innerHTML = '<i class="icon-ok matomo-copy-success-icon" aria-hidden="true"></i>';
      control.classList.remove('matomo-copy-reset');
      control.classList.add('matomo-copy-success');

      swaggerRoot[activeCopySuccessStateKey] = {
        element: control,
        originalInnerHTML,
        resetTimeoutId: window.setTimeout(() => {
          if (swaggerRoot[activeCopySuccessStateKey]?.element === control) {
            this.clearCopySuccessState(swaggerRoot);
          }
        }, 3000),
      };
    },
    disableAuthorizePlugin() {
      return {
        wrapComponents: {
          authorizeBtn: () => () => null,
        },
      };
    },
    attachSwaggerInteractionHandlers(swaggerRoot: SwaggerRootElement | null) {
      const interactiveSwaggerSelector = '.opblock-tag, .opblock-summary, .expand-operation, .opblock-summary-control';

      if (!swaggerRoot || swaggerRoot[summaryPathClickHandlerAttachedKey]) {
        return;
      }

      swaggerRoot[summaryPathClickHandlerAttachedKey] = true;
      swaggerRoot.addEventListener('click', (event) => {
        const target = event.target as Element | null;
        const flatTagHeader = this.getFlatTagHeader(target);

        if (flatTagHeader) {
          if (target?.closest('a')) {
            event.stopPropagation();
          } else {
            event.preventDefault();
            event.stopPropagation();
          }

          return;
        }

        const summaryPathCopyControl = this.getSummaryPathCopyControl(target);

        if (summaryPathCopyControl) {
          const originalInnerHTML = this.getOriginalCopyControlMarkup(
            swaggerRoot,
            summaryPathCopyControl,
          );

          window.setTimeout(() => {
            if (summaryPathCopyControl.isConnected) {
              this.showCopySuccessState(swaggerRoot, summaryPathCopyControl, originalInnerHTML);
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
      const container = document.getElementById(
        this.swaggerContainerId,
      ) as SwaggerRootElement | null;

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
      const container = document.getElementById(
        this.swaggerContainerId,
      ) as SwaggerRootElement | null;

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

      swaggerUiBundle({
        dom_id: `#${this.swaggerContainerId}`,
        spec: this.spec,
        deepLinking: true,
        docExpansion: 'list',
        defaultModelsExpandDepth: -1,
        layout: 'BaseLayout',
        tagsSorter: 'alpha',
        presets: swaggerUiBundle.presets?.apis ? [swaggerUiBundle.presets.apis] : [],
        plugins: [this.disableAuthorizePlugin],
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
  color: #3c4858;
  font-size: 14px;
  line-height: 1.5;
  padding-top: 0;
}
</style>
