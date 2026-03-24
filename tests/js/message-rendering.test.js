/**
 * Message rendering tests.
 */

describe('Message rendering', () => {
  beforeEach(() => {
    document.body.innerHTML = '';
    jest.resetModules();
    require('../../assets/src/widget/index.js');
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  test('messages container exists', () => {
    const messages = document.getElementById('ac-messages');
    expect(messages).not.toBeNull();
  });

  test('send button exists', () => {
    const send = document.getElementById('ac-send');
    expect(send).not.toBeNull();
  });

  test('CSAT elements exist when enabled', () => {
    const csat = document.getElementById('ac-csat');
    expect(csat).not.toBeNull();
  });

  test('CSAT has 5 star buttons', () => {
    const stars = document.querySelectorAll('.ac-csat__star');
    expect(stars.length).toBe(5);
  });

  test('file attach button exists when sharing enabled', () => {
    const attach = document.getElementById('ac-attach');
    expect(attach).not.toBeNull();
  });

  test('file input exists for attachment', () => {
    const fileInput = document.getElementById('ac-file-input');
    expect(fileInput).not.toBeNull();
    expect(fileInput.type).toBe('file');
  });

  test('transcript button exists', () => {
    const btn = document.getElementById('ac-transcript-btn');
    expect(btn).not.toBeNull();
  });
});
