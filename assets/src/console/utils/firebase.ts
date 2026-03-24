/**
 * Firebase utility — initializes Firebase and exports instances.
 */

declare const firebase: any;

const config = window.adventchatConfig;

let app: any = null;
let auth: any = null;
let db: any = null;

export function initFirebase() {
  if (app) return { app, auth, db };
  app = firebase.initializeApp(config.firebase);
  auth = firebase.auth();
  db = firebase.firestore();
  return { app, auth, db };
}

export function getAuth() {
  if (!auth) initFirebase();
  return auth;
}

export function getDb() {
  if (!db) initFirebase();
  return db;
}

export function getSiteId(): string {
  return config.siteId;
}

export function getRestUrl(): string {
  return config.restUrl;
}

export function getRestNonce(): string {
  return config.restNonce;
}

export function serverTimestamp() {
  return firebase.firestore.FieldValue.serverTimestamp();
}

export function increment(n: number) {
  return firebase.firestore.FieldValue.increment(n);
}
