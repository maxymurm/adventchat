/**
 * Offline detection and form tests.
 */

describe('Offline detection', () => {
  beforeEach(() => {
    document.body.innerHTML = '';
    jest.resetModules();
    require('../../assets/src/widget/index.js');
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  test('offline form has name, email, and message fields', () => {
    const name = document.getElementById('ac-off-name');
    const email = document.getElementById('ac-off-email');
    const msg = document.getElementById('ac-off-msg');
    expect(name).not.toBeNull();
    expect(email).not.toBeNull();
    expect(msg).not.toBeNull();
  });

  test('offline submit button exists', () => {
    const btn = document.getElementById('ac-off-submit');
    expect(btn).not.toBeNull();
  });

  test('offline success message is initially hidden', () => {
    const success = document.getElementById('ac-off-success');
    expect(success).not.toBeNull();
    expect(success.style.display).toBe('none');
  });

  test('offline form submit requires filled fields', () => {
    const name = document.getElementById('ac-off-name');
    const email = document.getElementById('ac-off-email');
    const msg = document.getElementById('ac-off-msg');
    const btn = document.getElementById('ac-off-submit');

    name.value = '';
    email.value = '';
    msg.value = '';

    btn.click();

    // Success message should still be hidden.
    const success = document.getElementById('ac-off-success');
    expect(success.style.display).toBe('none');
  });

  test('offline form submits when all fields filled', async () => {
    const name = document.getElementById('ac-off-name');
    const email = document.getElementById('ac-off-email');
    const msg = document.getElementById('ac-off-msg');
    const btn = document.getElementById('ac-off-submit');

    name.value = 'John Doe';
    email.value = 'john@example.com';
    msg.value = 'Need help';

    global.fetch.mockResolvedValueOnce({ ok: true });
    btn.click();

    // Button should be disabled after click.
    expect(btn.disabled).toBe(true);

    // Verify fetch was called.
    expect(global.fetch).toHaveBeenCalledWith(
      expect.stringContaining('/offline-message'),
      expect.objectContaining({ method: 'POST' })
    );
  });
});
