// @ts-check
const { test, expect } = require('@playwright/test');

const SHOP_URL = '/shop-2/';

/** Wait for a WooFlux REST fetch to complete. */
async function waitForProductsLoad(page) {
	await page.waitForResponse(
		(res) => res.url().includes('wooflux/v1/products') && res.status() === 200,
		{ timeout: 8000 }
	);
}

test.describe('WooFlux — product grid rendering', () => {
	test('renders product cards with image, title and price', async ({ page }) => {
		await page.goto(SHOP_URL);

		const cards = page.locator('.wooflux-product-card');
		await expect(cards.first()).toBeVisible();
		expect(await cards.count()).toBeGreaterThan(0);

		const firstCard = cards.first();
		await expect(firstCard.locator('.wooflux-product-image img')).toBeVisible();
		await expect(firstCard.locator('.wooflux-product-title')).toBeVisible();
		await expect(firstCard.locator('.wooflux-product-price')).toBeVisible();
	});

	test('product grid has CSS column variable', async ({ page }) => {
		await page.goto(SHOP_URL);

		// The product-grid.php template puts --wooflux-columns on the wrapper div
		const grid = page.locator('.wooflux-product-grid');
		await expect(grid).toBeVisible();

		const style = await grid.getAttribute('style');
		// May be on the element itself or as inline style from block wrapper
		const innerHTML = await page.content();
		expect(innerHTML).toMatch(/--wooflux-columns/);
	});

	test('results count is rendered', async ({ page }) => {
		await page.goto(SHOP_URL);
		const count = page.locator('.wooflux-results-count');
		await expect(count).toBeVisible();
		await expect(count).toContainText(/product/);
	});
});

test.describe('WooFlux — filter panel rendering', () => {
	test('filter panel renders with category checkboxes', async ({ page }) => {
		await page.goto(SHOP_URL);

		const panel = page.locator('.wooflux-filter-panel');
		await expect(panel).toBeVisible();

		const checkboxes = panel.locator('input[type="checkbox"][data-wp-on--change="actions.toggleCategory"]');
		expect(await checkboxes.count()).toBeGreaterThan(0);
	});

	test('filter panel renders price range inputs', async ({ page }) => {
		await page.goto(SHOP_URL);

		const priceRange = page.locator('.wooflux-price-range');
		await expect(priceRange).toBeVisible();

		const inputs = priceRange.locator('input[type="number"]');
		await expect(inputs).toHaveCount(2);
	});

	test('reset button is present', async ({ page }) => {
		await page.goto(SHOP_URL);
		const reset = page.locator('.wooflux-reset');
		await expect(reset).toBeVisible();
	});
});

test.describe('WooFlux — category filter interaction', () => {
	test('checking a category updates the URL', async ({ page }) => {
		await page.goto(SHOP_URL);

		const checkbox = page.locator('input[data-wp-on--change="actions.toggleCategory"]').first();
		await expect(checkbox).toBeVisible();
		await checkbox.check();

		await page.waitForURL(/wf_cat=/, { timeout: 8000 });
		expect(page.url()).toContain('wf_cat=');
	});

	test('products container updates after category filter', async ({ page }) => {
		await page.goto(SHOP_URL);

		const checkbox = page.locator('input[data-wp-on--change="actions.toggleCategory"]').first();
		await checkbox.check();

		// Wait for the REST fetch to complete
		await waitForProductsLoad(page);

		// Product grid should still have content or a no-products message
		const container = page.locator('.wooflux-products-container');
		await expect(container).toBeVisible();

		const cards = page.locator('.wooflux-product-card');
		const noProducts = page.locator('.wooflux-no-products');
		const hasCards = (await cards.count()) > 0;
		const hasNoMsg = (await noProducts.count()) > 0;
		expect(hasCards || hasNoMsg).toBe(true);
	});
});

test.describe('WooFlux — reset filters', () => {
	test('reset button clears URL params', async ({ page }) => {
		await page.goto(SHOP_URL);

		// Apply a filter first
		const checkbox = page.locator('input[data-wp-on--change="actions.toggleCategory"]').first();
		await checkbox.check();
		await page.waitForURL(/wf_cat=/, { timeout: 8000 });

		// Reset
		const [response] = await Promise.all([
			waitForProductsLoad(page),
			page.locator('.wooflux-reset').click(),
		]);

		await page.waitForURL((url) => !url.toString().includes('wf_'), { timeout: 8000 });
		expect(page.url()).not.toContain('wf_');
	});

	test('reset restores full product count', async ({ page }) => {
		await page.goto(SHOP_URL);

		const initialCount = await page.locator('.wooflux-product-card').count();

		const checkbox = page.locator('input[data-wp-on--change="actions.toggleCategory"]').first();
		await checkbox.check();
		await waitForProductsLoad(page);

		await page.locator('.wooflux-reset').click();
		await waitForProductsLoad(page);

		const finalCount = await page.locator('.wooflux-product-card').count();
		expect(finalCount).toBe(initialCount);
	});
});

test.describe('WooFlux — on-sale filter', () => {
	test('on-sale checkbox is checked after JS hydration when URL param is set', async ({ page }) => {
		await page.goto(SHOP_URL + '?wf_sale=1');

		const onSaleCheckbox = page.locator('input[data-wp-on--change="actions.toggleOnSale"]');

		// Wait for Interactivity API to hydrate and update the checked state
		await expect(onSaleCheckbox).toBeChecked({ timeout: 8000 });
	});
});
