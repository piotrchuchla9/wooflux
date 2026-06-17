import { buildQueryParams } from "../../src/store";

const baseFilters = {
  categories: [],
  priceMin: 0,
  priceMax: 0,
  onSale: false,
  inStock: false,
  attributes: {},
  rating: 0,
};

describe("buildQueryParams", () => {
  it("includes category IDs as array notation", () => {
    const filters = { ...baseFilters, categories: [5, 12] };
    const params = buildQueryParams(filters, 1);
    expect(params.getAll("categories[]")).toEqual(["5", "12"]);
  });

  it("omits categories when empty", () => {
    const params = buildQueryParams(baseFilters, 1);
    expect(params.has("categories[]")).toBe(false);
  });

  it("includes price_min when set", () => {
    const filters = { ...baseFilters, priceMin: 10 };
    const params = buildQueryParams(filters, 1);
    expect(params.get("price_min")).toBe("10");
  });

  it("includes price_max when set", () => {
    const filters = { ...baseFilters, priceMax: 100 };
    const params = buildQueryParams(filters, 1);
    expect(params.get("price_max")).toBe("100");
  });

  it("omits price when zero", () => {
    const params = buildQueryParams(baseFilters, 1);
    expect(params.has("price_min")).toBe(false);
    expect(params.has("price_max")).toBe(false);
  });

  it("sets on_sale=1 when true", () => {
    const filters = { ...baseFilters, onSale: true };
    const params = buildQueryParams(filters, 1);
    expect(params.get("on_sale")).toBe("1");
  });

  it("sets in_stock=1 when true", () => {
    const filters = { ...baseFilters, inStock: true };
    const params = buildQueryParams(filters, 1);
    expect(params.get("in_stock")).toBe("1");
  });

  it("includes rating when set", () => {
    const filters = { ...baseFilters, rating: 4 };
    const params = buildQueryParams(filters, 1);
    expect(params.get("rating")).toBe("4");
  });

  it("includes page when > 1", () => {
    const params = buildQueryParams(baseFilters, 3);
    expect(params.get("page")).toBe("3");
  });

  it("omits page 1", () => {
    const params = buildQueryParams(baseFilters, 1);
    expect(params.has("page")).toBe(false);
  });

  it("includes attribute filters", () => {
    const filters = { ...baseFilters, attributes: { pa_color: ["red", "blue"] } };
    const params = buildQueryParams(filters, 1);
    expect(params.get("attributes[pa_color]")).toBe("red,blue");
  });
});
