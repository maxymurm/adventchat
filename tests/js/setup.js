/**
 * Jest setup — mock browser globals and Firebase.
 */

// Mock Firebase.
global.firebase = {
  initializeApp: jest.fn(() => ({})),
  auth: jest.fn(() => ({
    onAuthStateChanged: jest.fn(),
    signInAnonymously: jest.fn(() => Promise.resolve()),
  })),
  firestore: jest.fn(() => ({
    collection: jest.fn(() => ({
      add: jest.fn(() => Promise.resolve({ id: 'test-session' })),
      doc: jest.fn(() => ({
        set: jest.fn(() => Promise.resolve()),
        update: jest.fn(() => Promise.resolve()),
        collection: jest.fn(() => ({
          add: jest.fn(() => Promise.resolve()),
          orderBy: jest.fn(() => ({
            onSnapshot: jest.fn(),
          })),
        })),
        onSnapshot: jest.fn(),
      })),
    })),
  })),
};

// Static Firestore helpers.
global.firebase.firestore.FieldValue = {
  serverTimestamp: jest.fn(() => new Date()),
  increment: jest.fn((n) => n),
};

// Mock adventchatConfig.
global.adventchatConfig = {
  firebase: {
    apiKey: 'test-key',
    authDomain: 'test.firebaseapp.com',
    projectId: 'test-project',
    storageBucket: 'test.appspot.com',
    messagingSenderId: '123456',
    appId: '1:123456:web:abc',
  },
  siteId: 'test-site-id',
  restUrl: 'http://localhost/wp-json/adventchat/v1',
  restNonce: 'test-nonce',
  settings: {
    position: 'bottom-right',
    offsetX: 20,
    offsetY: 20,
    primaryColor: '#0066ff',
    secondaryColor: '#ffffff',
    launcherStyle: 'bubble',
    launcherImage: '',
    welcomeTitle: 'Hi there!',
    welcomeSubtitle: 'How can we help you?',
    placeholder: 'Type a message…',
    soundEnabled: '1',
    autoOpenEnabled: '0',
    autoOpenDelay: 5,
    offlineEnabled: '1',
    gdprEnabled: '0',
    prechatEnabled: '1',
    csatEnabled: '1',
    fileSharing: '1',
  },
};

// Mock sessionStorage.
const storage = {};
global.sessionStorage = {
  getItem: jest.fn((key) => storage[key] || null),
  setItem: jest.fn((key, val) => { storage[key] = val; }),
  removeItem: jest.fn((key) => { delete storage[key]; }),
};

// Mock fetch.
global.fetch = jest.fn(() =>
  Promise.resolve({ ok: true, json: () => Promise.resolve({}) })
);
