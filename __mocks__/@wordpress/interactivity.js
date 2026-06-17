// Manual mock for @wordpress/interactivity — only used in Jest tests.
// The real module is provided by WordPress core at runtime.
module.exports = {
  store: jest.fn(() => ({ state: {}, actions: {} })),
  getConfig: jest.fn(() => ({})),
  getContext: jest.fn(() => ({})),
  getElement: jest.fn(() => ({})),
};
