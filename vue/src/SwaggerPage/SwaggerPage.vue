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
            class="searchInput browser-default"
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
            :class="[
              'card',
              'pluginCard',
              { 'pluginCard--expanded': expandedPluginName === plugin },
            ]"
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
              <SwaggerUiPanel :plugin="plugin" />
            </div>
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
  translate,
} from 'CoreHome';
import SwaggerUiPanel from './SwaggerUiPanel.vue';

interface SwaggerPageState {
  expandedPluginName: string | null;
  isLoading: boolean;
  loadError: string | null;
  plugins: string[];
  searchTerm: string;
}

export default defineComponent({
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

      AjaxHelper.fetch<string[]>(
        {
          method: 'OpenApiDocs.getPluginWhitelist',
        },
        {
          createErrorNotification: false,
        },
      ).then((plugins) => {
        this.plugins = [...plugins].sort((left, right) => left.localeCompare(right));
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
    togglePlugin(plugin: string) {
      if (this.expandedPluginName === plugin) {
        this.expandedPluginName = null;
        return;
      }

      this.expandedPluginName = plugin;
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
  width: 300px;
}

.searchIcon {
  position: absolute;
  top: 13px;
  left: 12px;
  color: #d0d5dd;
  font-size: 14px;
  pointer-events: none;
}

.searchInput {
  width: 100%;
  height: 38px;
  padding: 10px 12px 10px 38px;
  border: 1px solid #d0d5dd;
  border-radius: 8px;
  font-size: 14px;
  box-shadow: none;
}

.searchInput:focus-visible {
  border: 1px solid #5b8def;
  outline: 1px solid #5b8def;
}

.searchInput::placeholder {
  color: #98a2b3;
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
  animation: pluginCardOpen 180ms ease;
  transform-origin: top center;
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
  padding: 0;
}

.pluginBody::before {
  content: '';
  position: absolute;
  top: 0;
  left: 20px;
  right: 20px;
  border-top: 1px solid #e6edf5;
}

@keyframes pluginCardOpen {
  from {
    opacity: 0.72;
    transform: translateY(-4px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
