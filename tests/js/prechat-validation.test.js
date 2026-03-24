/**
 * Pre-chat form validation tests.
 */

describe('Pre-chat form validation', () => {
  beforeEach(() => {
    document.body.innerHTML = '';
    // Reset module cache so IIFE re-executes.
    jest.resetModules();
    require('../../assets/src/widget/index.js');
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  test('name and email fields exist', () => {
    const name = document.getElementById('ac-name');
    const email = document.getElementById('ac-email');
    expect(name).not.toBeNull();
    expect(email).not.toBeNull();
  });

  test('start chat button exists', () => {
    const btn = document.getElementById('ac-start-chat');
    expect(btn).not.toBeNull();
  });

  test('clicking start without name does not start chat', () => {
    const nameInput = document.getElementById('ac-name');
    const emailInput = document.getElementById('ac-email');
    const btn = document.getElementById('ac-start-chat');

    nameInput.value = '';
    emailInput.value = 'test@test.com';

    const focusSpy = jest.spyOn(nameInput, 'focus');
    btn.click();

    // Pre-chat form should still be visible.
    const prechat = document.getElementById('ac-prechat');
    expect(prechat.style.display).not.toBe('none');
  });

  test('clicking start without email does not start chat', () => {
    const nameInput = document.getElementById('ac-name');
    const emailInput = document.getElementById('ac-email');
    const btn = document.getElementById('ac-start-chat');

    nameInput.value = 'John';
    emailInput.value = '';

    btn.click();

    const prechat = document.getElementById('ac-prechat');
    expect(prechat.style.display).not.toBe('none');
  });

  test('GDPR checkbox is absent when disabled', () => {
    // Default config has gdprEnabled = '0'.
    const consent = document.getElementById('ac-consent');
    expect(consent).toBeNull();
  });
});
