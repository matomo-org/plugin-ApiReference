/*!
 * Matomo - free/libre analytics platform
 *
 * ApiReference screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('ApiReference', function () {
    this.fixture = 'Piwik\\Plugins\\ApiReference\\tests\\Fixtures\\SwaggerPageFixture';

    const pageUrl = '?module=ApiReference&action=swagger&idSite=1&period=day&date=2010-01-03';
    const targetPlugin = 'Bandwidth';
    const searchInputSelector = '.searchInput';

    before(function () {
        testEnvironment.pluginsToLoad = ['ApiReference', 'Bandwidth'];
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.overrideConfig('General', 'enable_auto_update', 0);
        testEnvironment.save();
    });

    after(function () {
        delete testEnvironment.pluginsToLoad;
        delete testEnvironment.configOverride.General;
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    async function moveMouseAway() {
        await page.mouse.move(-10, -10);
    }

    async function waitForUiToSettle(delay = 150) {
        await page.waitForNetworkIdle();
        await page.waitForTimeout(delay);
    }

    async function loadSwaggerPage() {
        await page.goto(pageUrl);
        await page.waitForSelector(searchInputSelector);
        await page.waitForSelector('.pluginList .pluginCard');
        await waitForUiToSettle();
        await moveMouseAway();
    }

    async function searchFor(term) {
        await page.evaluate((selector, value) => {
            const input = document.querySelector(selector);

            if (!input) {
                throw new Error(`Unable to find ${selector}`);
            }

            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }, searchInputSelector, term);
        await page.waitForTimeout(150);
        await moveMouseAway();
    }

    async function expandPlugin(pluginName) {
        await page.evaluate((targetName) => {
            const pluginButtons = Array.from(document.querySelectorAll('.pluginToggle'));
            const pluginButton = pluginButtons.find((button) => {
                const pluginNameElement = button.querySelector('.pluginName');
                return pluginNameElement && pluginNameElement.textContent.trim() === targetName;
            });

            if (!pluginButton) {
                throw new Error(`Unable to find plugin card for ${targetName}`);
            }

            pluginButton.click();
        }, pluginName);

        await page.waitForSelector('.pluginCard--expanded');
        await page.waitForSelector('.pluginCard--expanded .swaggerMount--ready');
        await page.waitForSelector('.pluginCard--expanded .swagger-ui .opblock-tag-section');
        await waitForUiToSettle(250);
        await moveMouseAway();
    }

    async function waitForSingleVisiblePlugin(pluginName) {
        await page.waitForFunction((expectedPluginName) => {
            const visiblePluginNames = Array.from(document.querySelectorAll('.pluginCard'))
                .filter((card) => card.offsetParent !== null)
                .map((card) => card.querySelector('.pluginName')?.textContent?.trim())
                .filter(Boolean);

            return visiblePluginNames.length === 1 && visiblePluginNames[0] === expectedPluginName;
        }, {}, pluginName);
    }

    it('should show the filtered plugin search result', async function () {
        await loadSwaggerPage();
        await searchFor('bandwidth');
        await waitForSingleVisiblePlugin(targetPlugin);

        expect(await page.screenshotSelector('.searchBar,.pluginList')).to.matchImage('filtered_plugin');
    });

    it('should show the empty plugin search result', async function () {
        await loadSwaggerPage();
        await searchFor('no-plugin-match');
        await page.waitForSelector('.emptyText');

        expect(await page.screenshotSelector('.searchBar,.emptyText')).to.matchImage('empty_search');
    });

    it('should show the expanded Swagger UI for a plugin', async function () {
        await loadSwaggerPage();
        await searchFor('bandwidth');
        await waitForSingleVisiblePlugin(targetPlugin);
        await expandPlugin(targetPlugin);

        expect(await page.screenshotSelector('.pluginCard--expanded')).to.matchImage('expanded_plugin');
    });
});
