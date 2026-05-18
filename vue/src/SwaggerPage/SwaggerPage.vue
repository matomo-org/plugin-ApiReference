<!--
  Matomo - free/libre analytics platform

  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="page">
    <div v-content-intro>
      <h2>{{ translate('ApiReference_SwaggerApi') }}</h2>
    </div>

    <ContentBlock :content-title="translate('ApiReference_ReportingApiReference')">
      <p>{{ translate('ApiReference_ReportingApiSummary') }}</p>
      <p v-html="$sanitize(reportingApiMoreInformation)" />
    </ContentBlock>

    <ContentBlock :content-title="translate('ApiReference_UserAuthentication')">
      <p v-html="$sanitize(userAuthenticationHelp)" />
      <p>
        <a :href="userSecurityUrl">
          {{ translate('ApiReference_UserAuthenticationManageTokens') }}
        </a>
      </p>
    </ContentBlock>

    <ContentBlock>
      <ActivityIndicator :loading="isLoading" />
      <Alert v-if="loadError" severity="danger">
        {{ loadError }}
      </Alert>

      <p v-else-if="!isLoading && plugins.length === 0">
        {{ translate('ApiReference_SwaggerPagePluginEmpty') }}
      </p>

      <div v-else-if="!isLoading">
        <div class="searchBar">
          <span class="searchIcon icon-search" />
          <input
            v-model="searchTerm"
            type="text"
            class="searchInput browser-default"
            :placeholder="translate('ApiReference_SwaggerPageSearchPlaceholder')"
          >
        </div>

        <p v-if="filteredPlugins.length === 0" class="emptyText">
          {{ translate('ApiReference_SwaggerPageSearchNoResults') }}
        </p>

        <div
          v-else
          class="pluginList"
        >
          <div
            v-for="plugin in plugins"
            :key="plugin"
            v-show="filteredPluginSet.has(plugin)"
            :class="[
              'card',
              'pluginCard',
              { 'pluginCard--expanded': expandedPluginName === plugin },
            ]"
            @mouseenter="prefetchPluginSpec(plugin)"
          >
            <button
              type="button"
              class="pluginToggle"
              :aria-expanded="expandedPluginName === plugin ? 'true' : 'false'"
              @focus="prefetchPluginSpec(plugin)"
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

            <transition
              name="pluginBodyTransition"
              @before-enter="onPluginBodyBeforeEnter"
              @enter="onPluginBodyEnter"
              @before-leave="onPluginBodyBeforeLeave"
              @leave="onPluginBodyLeave"
              @after-enter="resetPluginBodyTransitionStyles"
              @after-leave="resetPluginBodyTransitionStyles"
            >
              <div
                v-show="expandedPluginName === plugin"
                class="card-content pluginBody"
              >
                <SwaggerUiPanel
                  :plugin="plugin"
                  :piwik-url="piwikUrl"
                  :spec="getPluginSpecState(plugin).spec"
                  :is-loading="getPluginSpecState(plugin).status === 'loading'"
                  :spec-load-error="getPluginSpecState(plugin).loadError"
                />
              </div>
            </transition>
          </div>
        </div>
      </div>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ActivityIndicator,
  AjaxHelper,
  Alert,
  ContentBlock,
  ContentIntro,
  externalLink,
  MatomoUrl,
  translate,
} from 'CoreHome';
import SwaggerUiPanel from './SwaggerUiPanel.vue';

type PluginSpecStatus = 'idle' | 'loading' | 'loaded' | 'error';

interface OpenApiSpec {
  [key: string]: unknown;
}

interface PluginSpecState {
  loadError: string | null;
  request: Promise<OpenApiSpec | null> | null;
  spec: OpenApiSpec | null;
  status: PluginSpecStatus;
}

interface SwaggerPageState {
  expandedPluginName: string | null;
  isLoading: boolean;
  loadError: string | null;
  plugins: string[];
  pluginSpecs: Record<string, PluginSpecState>;
  searchTerm: string;
}

export default defineComponent({
  props: {
    piwikUrl: {
      type: String,
      default: null,
    },
  },
  components: {
    ActivityIndicator,
    Alert,
    ContentBlock,
    SwaggerUiPanel,
  },
  directives: {
    ContentIntro,
  },
  computed: {
    reportingApiMoreInformation(): string {
      return translate(
        'ApiReference_ReportingApiMoreInformation',
        externalLink('https://matomo.org/docs/analytics-api'),
        '</a>',
        externalLink('https://developer.matomo.org/api-reference/reporting-api'),
        '</a>',
      );
    },
    userAuthenticationHelp(): string {
      const usingTokenAuth = translate(
        'ApiReference_UserAuthenticationUsingTokenAuth',
        '',
        '',
        '<code>token_auth</code>',
      );

      const learnMore = translate(
        'CoreHome_LearnMoreFullStop',
        externalLink('https://developer.matomo.org/api-reference/reporting-api#authenticate-to-the-api-via-token_auth-parameter'),
        '</a>',
      );

      return `${usingTokenAuth} ${learnMore}`;
    },
    userSecurityUrl(): string {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'userSecurity',
      })}#/#authtokens`;
    },
    filteredPlugins(): string[] {
      const searchTerm = this.searchTerm.trim().toLowerCase();

      if (!searchTerm) {
        return this.plugins;
      }

      return this.plugins.filter((plugin) => plugin.toLowerCase().includes(searchTerm));
    },
    filteredPluginSet(): Set<string> {
      return new Set(this.filteredPlugins);
    },
  },
  data(): SwaggerPageState {
    return {
      expandedPluginName: null,
      isLoading: false,
      loadError: null,
      plugins: [],
      pluginSpecs: {},
      searchTerm: '',
    };
  },
  created() {
    this.fetchPlugins();
  },
  watch: {
    searchTerm(value: string) {
      if (this.expandedPluginName && !this.matchesSearch(this.expandedPluginName, value)) {
        this.expandedPluginName = null;
      }
    },
  },
  methods: {
    matchesSearch(plugin: string, searchTerm?: string) {
      const normalizedSearchTerm = (searchTerm ?? this.searchTerm).trim().toLowerCase();
      return plugin.toLowerCase().includes(normalizedSearchTerm);
    },
    forceReflow(element: HTMLElement) {
      element.getBoundingClientRect();
    },
    getPluginBodyTransitionDuration(height: number) {
      return Math.min(400, Math.max(180, Math.round(height / 4)));
    },
    resetPluginBodyTransitionStyles(element: Element) {
      const htmlElement = element as HTMLElement;
      htmlElement.style.height = '';
      htmlElement.style.transitionDuration = '';
      htmlElement.style.overflow = '';
    },
    setPluginBodyTransitionState(element: HTMLElement, height: string) {
      element.style.height = height;
      element.style.overflow = 'hidden';
    },
    transitionPluginBody(element: HTMLElement, startHeight: string, endHeight: string) {
      this.setPluginBodyTransitionState(element, startHeight);
      element.style.transitionDuration = `${this.getPluginBodyTransitionDuration(element.scrollHeight)}ms`;
      this.forceReflow(element);
      element.style.height = endHeight;
    },
    onPluginBodyBeforeEnter(element: Element) {
      this.setPluginBodyTransitionState(element as HTMLElement, '0');
    },
    onPluginBodyEnter(element: Element) {
      const htmlElement = element as HTMLElement;
      this.transitionPluginBody(htmlElement, '0', `${htmlElement.scrollHeight}px`);
    },
    onPluginBodyBeforeLeave(element: Element) {
      const htmlElement = element as HTMLElement;
      this.setPluginBodyTransitionState(htmlElement, `${htmlElement.scrollHeight}px`);
    },
    onPluginBodyLeave(element: Element) {
      const htmlElement = element as HTMLElement;
      this.transitionPluginBody(htmlElement, `${htmlElement.scrollHeight}px`, '0');
    },
    async fetchPlugins() {
      this.expandedPluginName = null;
      this.isLoading = true;
      this.loadError = null;
      this.plugins = [];
      this.pluginSpecs = {};
      this.searchTerm = '';

      try {
        const plugins = await AjaxHelper.fetch<string[]>(
          {
            method: 'ApiReference.getAllowedPlugins',
          },
          {
            createErrorNotification: false,
          },
        );
        this.plugins = [...plugins].sort((left, right) => left.localeCompare(right));
      } catch {
        this.loadError = translate('ApiReference_SwaggerPageRequestFailed');
      } finally {
        this.isLoading = false;
      }
    },
    createPluginSpecState(): PluginSpecState {
      return {
        loadError: null,
        request: null,
        spec: null,
        status: 'idle',
      };
    },
    getPluginSpecState(plugin: string): PluginSpecState {
      if (!this.pluginSpecs[plugin]) {
        this.pluginSpecs[plugin] = this.createPluginSpecState();
      }

      return this.pluginSpecs[plugin];
    },
    async prefetchPluginSpec(plugin: string, forceReload = false): Promise<OpenApiSpec | null> {
      const state = this.getPluginSpecState(plugin);

      if (!forceReload) {
        if (state.status === 'loaded') {
          return state.spec;
        }

        if (state.request) {
          return state.request;
        }
      }

      state.status = 'loading';
      state.loadError = null;

      state.request = (async () => {
        try {
          const spec = await AjaxHelper.fetch<OpenApiSpec>(
            {
              method: 'ApiReference.getOpenApiSpec',
              pluginName: plugin,
              format: 'json',
            },
            {
              createErrorNotification: false,
            },
          );
          state.spec = spec;
          state.status = 'loaded';
          return spec;
        } catch {
          state.spec = null;
          state.status = 'error';
          state.loadError = translate('ApiReference_SwaggerPageSpecLoadFailed');
          return null;
        } finally {
          state.request = null;
        }
      })();

      return state.request;
    },
    togglePlugin(plugin: string) {
      if (this.expandedPluginName === plugin) {
        this.expandedPluginName = null;
        return;
      }

      this.expandedPluginName = plugin;

      const state = this.getPluginSpecState(plugin);
      if (state.status !== 'loaded') {
        this.prefetchPluginSpec(plugin, state.status === 'error');
      }
    },
  },
});
</script>

<style scoped>
.page {
  color: var(--theme-color-text, #3b4151);
}

.searchBar {
  position: relative;
  margin-bottom: 1.5rem;
  width: 300px;
}

.searchIcon {
  position: absolute;
  top: 13px;
  left: 12px;
  color: var(--theme-color-text-lighter, #98a2b3);
  font-size: 14px;
  pointer-events: none;
}

.searchInput {
  width: 100%;
  height: 38px;
  padding: 10px 12px 10px 38px;
  background: var(--theme-color-background-contrast, #fff);
  border: 1px solid var(--theme-color-border, #d0d5dd);
  border-radius: 8px;
  color: var(--theme-color-text, #3b4151);
  font-size: 14px;
  box-shadow: none;
}

.searchInput:focus-visible {
  border: 1px solid var(--theme-color-focus-ring, #5b8def);
  outline: 1px solid var(--theme-color-focus-ring, #5b8def);
}

.searchInput::placeholder {
  color: var(--theme-color-text-lighter, #98a2b3);
}

.emptyText {
  margin-bottom: 0;
  color: var(--theme-color-text-light, #646464);
}

.pluginCard {
  background: var(--theme-color-background-contrast, #fff);
  border: 1px solid var(--theme-color-border, #d9e2ec);
  border-radius: 4px;
  box-shadow: none;
  overflow: hidden;
  transition: border-color 180ms ease;
}

.pluginCard--expanded {
  border-color: var(--theme-color-border, #cfd8e3);
  transform-origin: top center;
}

.pluginToggle {
  width: 100%;
  padding: 16px 20px;
  border: 0;
  outline: none;
  background: var(--theme-color-background-contrast, #fff);
  display: flex;
  align-items: center;
  color: inherit;
  cursor: pointer;
  font: inherit;
  text-align: left;
}

.pluginToggle:focus-visible {
  box-shadow: inset 0 0 0 2px var(--theme-color-focus-ring, #cfd8e3);
}

.pluginHeader {
  display: flex;
  align-items: center;
  gap: 12px;
}

.pluginChevron {
  flex: 0 0 12px;
  color: var(--theme-color-text-light, #5b6b7c);
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
  padding: 0;
}

.pluginBody::before {
  content: '';
  position: absolute;
  top: 0;
  left: 20px;
  right: 20px;
  border-top: 1px solid var(--theme-color-border, #e6edf5);
}

</style>
