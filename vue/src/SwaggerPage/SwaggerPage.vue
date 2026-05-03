<!--
  Matomo - free/libre analytics platform

  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="page">
    <div v-content-intro>
      <h2>{{ translate('OpenApiDocs_SwaggerPageTitle') }}</h2>
      <p>{{ translate('OpenApiDocs_SwaggerPageDescription') }}</p>
    </div>

    <ContentBlock>
      <ActivityIndicator :loading="isLoading" />

      <p v-if="isLoading" class="loadingText">
        {{ translate('OpenApiDocs_SwaggerPageLoading') }}
      </p>

      <Alert v-if="loadError" severity="danger">
        {{ loadError }}
      </Alert>

      <p v-else-if="!isLoading && plugins.length === 0">
        {{ translate('OpenApiDocs_SwaggerPagePluginEmpty') }}
      </p>

      <div v-else-if="!isLoading">
        <div class="searchBar">
          <span class="searchIcon icon-search" />
          <input
            :value="searchTerm"
            type="text"
            class="searchInput"
            :placeholder="translate('OpenApiDocs_SwaggerPageSearchPlaceholder')"
            @input="onSearchInput"
          >
        </div>

        <p v-if="filteredPlugins.length === 0" class="emptyText">
          {{ translate('OpenApiDocs_SwaggerPageSearchNoResults') }}
        </p>

        <div
          v-else
          class="pluginList"
        >
          <div
            v-for="plugin in filteredPlugins"
            :key="plugin"
            :class="['card', 'pluginCard', { 'pluginCard--expanded': expandedPluginName === plugin }]"
          >
            <button
              type="button"
              class="pluginToggle"
              :aria-expanded="expandedPluginName === plugin ? 'true' : 'false'"
              @click="togglePlugin(plugin)"
            >
              <span class="pluginHeader">
                <span
                  :class="[
                    'pluginChevron',
                    expandedPluginName === plugin ? 'icon-chevron-down' : 'icon-chevron-right',
                  ]"
                />
                <span class="pluginName">{{ plugin }}</span>
              </span>
            </button>

            <div
              v-if="expandedPluginName === plugin"
              class="card-content pluginBody"
            >
              <div class="swaggerScope">
                <Alert
                  v-if="swaggerErrors[plugin]"
                  severity="danger"
                >
                  {{ swaggerErrors[plugin] }}
                </Alert>

                <div
                  :id="getSwaggerContainerId(plugin)"
                  class="swaggerMount"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent, nextTick } from 'vue';
import {
  ActivityIndicator,
  AjaxHelper,
  Alert,
  ContentBlock,
  ContentIntro,
  translate,
} from 'CoreHome';

type SwaggerUiFactory = (config: {
  dom_id: string;
  deepLinking: boolean;
  defaultModelsExpandDepth: number;
  docExpansion: 'list' | 'full' | 'none';
  layout: string;
  onComplete?: () => void;
  presets?: unknown[];
  plugins?: Array<() => unknown>;
  requestInterceptor?: (request: { loadSpec?: boolean; url: string }) => { loadSpec?: boolean; url: string };
  tagsSorter?: string;
  url: string;
}) => unknown;

type SwaggerRootElement = HTMLElement & {
  __matomoActiveCopySuccessState?: {
    element: HTMLElement;
    originalInnerHTML: string;
    resetTimeoutId: number;
  } | null;
  __matomoSummaryPathClickHandlerAttached?: boolean;
};

type SwaggerWindow = Window & {
  SwaggerUIBundle?: SwaggerUiFactory & {
    presets?: {
      apis?: unknown;
    };
  };
};

interface SwaggerPageState {
  expandedPluginName: string | null;
  isLoading: boolean;
  loadError: string | null;
  plugins: string[];
  searchTerm: string;
  swaggerErrors: Record<string, string | null>;
}

export default defineComponent({
  components: {
    ActivityIndicator,
    Alert,
    ContentBlock,
  },
  directives: {
    ContentIntro,
  },
  computed: {
    filteredPlugins(): string[] {
      const searchTerm = this.searchTerm.trim().toLowerCase();

      if (!searchTerm) {
        return this.plugins;
      }

      return this.plugins.filter((plugin) => plugin.toLowerCase().includes(searchTerm));
    },
  },
  data(): SwaggerPageState {
    return {
      expandedPluginName: null,
      isLoading: false,
      loadError: null,
      plugins: [],
      searchTerm: '',
      swaggerErrors: {},
    };
  },
  created() {
    this.fetchPlugins();
  },
  methods: {
    fetchPlugins() {
      this.expandedPluginName = null;
      this.isLoading = true;
      this.loadError = null;
      this.plugins = [];
      this.searchTerm = '';
      this.swaggerErrors = {};

      AjaxHelper.fetch<string[]>(
        {
          method: 'OpenApiDocs.getPluginWhitelist',
        },
        {
          createErrorNotification: false,
        },
      ).then((plugins) => {
        this.plugins = plugins;
      }).catch(() => {
        this.loadError = translate('OpenApiDocs_SwaggerPageRequestFailed');
      }).finally(() => {
        this.isLoading = false;
      });
    },
    onSearchInput(event: Event) {
      const target = event.target as HTMLInputElement | null;
      this.onSearchTermChange(target?.value || '');
    },
    onSearchTermChange(value: string) {
      this.searchTerm = value;

      if (this.expandedPluginName && !this.filteredPlugins.includes(this.expandedPluginName)) {
        this.expandedPluginName = null;
      }
    },
    async togglePlugin(plugin: string) {
      if (this.expandedPluginName === plugin) {
        this.expandedPluginName = null;
        return;
      }

      this.expandedPluginName = plugin;
      this.swaggerErrors[plugin] = null;

      await nextTick();
      this.renderSwaggerUi(plugin);
    },
    getSwaggerContainerId(plugin: string) {
      return `swagger-ui-${plugin}`;
    },
    getSwaggerSpecUrl(plugin: string) {
      const params = new URLSearchParams({
        module: 'API',
        method: 'OpenApiDocs.getGeneratedOpenApiSpec',
        plugin,
        format: 'JSON',
      });

      return `index.php?${params.toString()}`;
    },
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
    normalizeSwaggerUi(swaggerRoot: ParentNode | null) {
      if (!swaggerRoot) {
        return;
      }

      this.shortenSummaryPaths(swaggerRoot);
      this.updateFlatSingleTag(swaggerRoot);
    },
    getSummaryPathCopyControl(target: Element | null) {
      return target?.closest('.opblock-summary .view-line-link.copy-to-clipboard') as HTMLElement | null;
    },
    getFlatTagHeader(target: Element | null) {
      return target?.closest('.opblock-tag-section.matomo-flat-tag > .opblock-tag') as HTMLElement | null;
    },
    getOriginalCopyControlMarkup(swaggerRoot: SwaggerRootElement, control: HTMLElement) {
      const state = swaggerRoot.__matomoActiveCopySuccessState;

      if (state && state.element === control) {
        return state.originalInnerHTML;
      }

      return control.innerHTML;
    },
    clearCopySuccessState(swaggerRoot: SwaggerRootElement) {
      const state = swaggerRoot.__matomoActiveCopySuccessState;

      if (!state) {
        return;
      }

      window.clearTimeout(state.resetTimeoutId);
      state.element.innerHTML = state.originalInnerHTML;
      state.element.classList.remove('matomo-copy-success');
      state.element.classList.remove('matomo-copy-reset');
      void state.element.offsetWidth;
      state.element.classList.add('matomo-copy-reset');
      swaggerRoot.__matomoActiveCopySuccessState = null;
    },
    showCopySuccessState(swaggerRoot: SwaggerRootElement, control: HTMLElement, originalInnerHTML: string) {
      this.clearCopySuccessState(swaggerRoot);

      control.innerHTML = '<i class="icon-ok matomo-copy-success-icon" aria-hidden="true"></i>';
      control.classList.remove('matomo-copy-reset');
      control.classList.add('matomo-copy-success');

      swaggerRoot.__matomoActiveCopySuccessState = {
        element: control,
        originalInnerHTML,
        resetTimeoutId: window.setTimeout(() => {
          if (swaggerRoot.__matomoActiveCopySuccessState?.element === control) {
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

      if (!swaggerRoot || swaggerRoot.__matomoSummaryPathClickHandlerAttached) {
        return;
      }

      swaggerRoot.__matomoSummaryPathClickHandlerAttached = true;
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
          const originalInnerHTML = this.getOriginalCopyControlMarkup(swaggerRoot, summaryPathCopyControl);

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
    renderSwaggerUi(plugin: string) {
      const swaggerUiBundle = (window as SwaggerWindow).SwaggerUIBundle;
      const containerId = this.getSwaggerContainerId(plugin);
      const container = document.getElementById(containerId) as SwaggerRootElement | null;

      if (!swaggerUiBundle || !container) {
        this.swaggerErrors[plugin] = translate('OpenApiDocs_SwaggerPageSpecLoadFailed');
        return;
      }

      container.innerHTML = '';

      swaggerUiBundle({
        dom_id: `#${containerId}`,
        url: this.getSwaggerSpecUrl(plugin),
        deepLinking: true,
        docExpansion: 'list',
        defaultModelsExpandDepth: -1,
        layout: 'BaseLayout',
        tagsSorter: 'alpha',
        presets: swaggerUiBundle.presets?.apis ? [swaggerUiBundle.presets.apis] : [],
        plugins: [this.disableAuthorizePlugin],
        requestInterceptor: (request) => {
          if (request.loadSpec && request.url.includes('OpenApiDocs.getGeneratedOpenApiSpec')) {
            return {
              ...request,
              url: this.getSwaggerSpecUrl(plugin),
            };
          }

          return request;
        },
        onComplete: () => {
          window.setTimeout(() => {
            this.normalizeSwaggerUi(container);
          }, 0);

          this.attachSwaggerInteractionHandlers(container);
        },
      });
    },
  },
});
</script>

<style scoped>
.loadingText {
  margin-bottom: 1rem;
}

.searchBar {
  position: relative;
  margin-bottom: 1.5rem;
}

.searchIcon {
  position: absolute;
  top: 50%;
  left: 14px;
  transform: translateY(-50%);
  color: #98a3b3;
  font-size: 14px;
  pointer-events: none;
}

.searchInput {
  width: 100%;
  height: 42px;
  margin: 0;
  padding: 0 16px 0 40px;
  border: 1px solid #d9dee7;
  border-radius: 8px;
  box-sizing: border-box;
  background: #fff;
  box-shadow: none;
  color: #1f2933;
  font: inherit;
}

.searchInput::placeholder {
  color: #98a3b3;
}

.searchInput:focus {
  border-color: #c7d2df;
  box-shadow: 0 0 0 1px #c7d2df;
  outline: none;
}

.emptyText {
  margin-bottom: 0;
}

.pluginList {
  display: flex;
  flex-direction: column;
}

.pluginCard {
  margin-top: 0;
  border: 1px solid #d9e2ec;
  border-radius: 4px;
  box-shadow: none;
  overflow: hidden;
}

.pluginCard--expanded {
  border-color: #cfd8e3;
}

.pluginToggle {
  width: 100%;
  padding: 16px 20px;
  border: 0;
  outline: none;
  background: #fff;
  display: flex;
  align-items: center;
  color: inherit;
  cursor: pointer;
  font: inherit;
  text-align: left;
}

.pluginToggle:focus,
.pluginToggle:active {
  outline: none;
}

.pluginToggle:focus-visible {
  box-shadow: inset 0 0 0 2px #cfd8e3;
}

.pluginHeader {
  display: flex;
  align-items: center;
  gap: 12px;
}

.pluginChevron {
  width: 12px;
  min-width: 12px;
  color: #5b6b7c;
  font-size: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.pluginName {
  font-size: 15px;
  font-weight: 500;
}

.pluginBody {
  position: relative;
  padding: 20px;
}

.pluginBody::before {
  content: '';
  position: absolute;
  top: 0;
  left: 20px;
  right: 20px;
  border-top: 1px solid #e6edf5;
}

.swaggerMount {
  min-height: 180px;
}

.swaggerScope :deep(.swagger-ui) {
  border: 0;
  border-radius: 0;
  color: #3c4858;
  font-size: 14px;
  line-height: 1.5;
  padding-top: 0;
}
</style>
