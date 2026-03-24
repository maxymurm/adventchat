/**
 * Widget initialization and DOM injection tests.
 */

describe('Widget initialization', () => {
  beforeEach(() => {
    document.body.innerHTML = '';
    jest.resetModules();
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  function loadWidget() {
    require('../../assets/src/widget/index.js');
  }

  test('widget DOM is injected into body', () => {
    loadWidget();
    const widget = document.querySelector('.adventchat-widget');
    expect(widget).not.toBeNull();
  });

  test('widget has launcher button', () => {
    loadWidget();
    const launcher = document.getElementById('ac-launcher');
    expect(launcher).not.toBeNull();
    expect(launcher.tagName).toBe('BUTTON');
  });

  test('widget has chat window', () => {
    loadWidget();
    const chatWindow = document.getElementById('ac-window');
    expect(chatWindow).not.toBeNull();
  });

  test('widget position defaults to bottom-right', () => {
    loadWidget();
    const widget = document.querySelector('.adventchat-widget');
    expect(widget.classList.contains('adventchat-widget--bottom-right')).toBe(true);
  });

  test('pre-chat form is present when enabled', () => {
    loadWidget();
    const prechat = document.getElementById('ac-prechat');
    expect(prechat).not.toBeNull();
  });

  test('offline form is present when enabled', () => {
    loadWidget();
    const offline = document.getElementById('ac-offline');
    expect(offline).not.toBeNull();
  });

  test('badge element exists', () => {
    loadWidget();
    const badge = document.getElementById('ac-badge');
    expect(badge).not.toBeNull();
    expect(badge.textContent).toBe('0');
  });
});
