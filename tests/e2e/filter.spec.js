import { test, expect } from "@wordpress/e2e-test-utils-playwright";

test.describe("WooFlux filter interactions", () => {
  test("Category filter updates products and URL", async ({ page, admin, editor }) => {
    // This test requires a WP environment with WooFlux active and a shop page set up.
    await page.goto("/shop/");

    const categoryCheckbox = page.locator(
      '[data-wp-on--change="actions.toggleCategory"]'
    ).first();

    if (!(await categoryCheckbox.isVisible())) {
      test.skip();
      return;
    }

    await categoryCheckbox.check();

    // Wait for loading spinner to disappear.
    await page.waitForSelector(".wooflux-loading[hidden]", { timeout: 5000 });

    // URL should reflect the filter.
    expect(page.url()).toContain("wf_cat=");

    // Products container should have content.
    const container = page.locator(".wooflux-products-container");
    expect((await container.innerHTML()).length).toBeGreaterThan(0);
  });

  test("Reset filters clears all active filters", async ({ page }) => {
    await page.goto("/shop/?wf_sale=1");

    // Wait for page load.
    await page.waitForSelector(".wooflux-filter-panel");

    const resetButton = page.locator(".wooflux-reset");
    await resetButton.click();

    await page.waitForSelector(".wooflux-loading[hidden]", { timeout: 5000 });

    expect(page.url()).not.toContain("wf_");
  });
});
