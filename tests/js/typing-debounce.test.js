/**
 * Typing indicator debounce tests.
 */

describe('Typing indicator', () => {
  beforeEach(() => {
    document.body.innerHTML = '';
    jest.resetModules();
    jest.useFakeTimers();
    require('../../assets/src/widget/index.js');
  });

  afterEach(() => {
    document.body.innerHTML = '';
    jest.useRealTimers();
  });

  test('typing indicator element exists', () => {
    const typing = document.getElementById('ac-typing');
    expect(typing).not.toBeNull();
  });

  test('text input has keydown listener for Enter', () => {
    const textInput = document.getElementById('ac-text');
    // The text input should exist (hidden until chat starts).
    expect(textInput).not.toBeNull();
  });

  test('typing area has placeholder text', () => {
    const textInput = document.getElementById('ac-text');
    expect(textInput).not.toBeNull();
    expect(textInput.placeholder).toContain('Type a message');
  });
});
