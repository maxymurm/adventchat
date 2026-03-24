/**
 * Shared TypeScript types for the operator console.
 */

export interface FirebaseConfig {
  apiKey: string;
  authDomain: string;
  projectId: string;
  storageBucket?: string;
  messagingSenderId?: string;
  appId?: string;
}

export interface AdventChatConfig {
  firebase: FirebaseConfig;
  siteId: string;
  restUrl: string;
  restNonce: string;
}

export interface ChatSession {
  id: string;
  siteId: string;
  visitorUid: string;
  visitorName: string;
  visitorEmail: string;
  status: 'waiting' | 'active' | 'ended';
  department: string;
  agentUid: string;
  agentName: string;
  startedAt: any;
  lastMessageAt: any;
  visitorInfo: VisitorInfo;
  messageCount: number;
  rating: number;
  ratingComment: string;
}

export interface VisitorInfo {
  userAgent: string;
  language: string;
  pageUrl: string;
  pageTitle: string;
  referrer: string;
  screenWidth: number;
  screenHeight: number;
  timezone: string;
}

export interface ChatMessage {
  id: string;
  senderUid: string;
  senderName: string;
  senderType: 'visitor' | 'agent' | 'system';
  text: string;
  timestamp: any;
  readByAgent: boolean;
  readByVisitor: boolean;
  isNote?: boolean;
}

export interface Agent {
  id: string;
  uid: string;
  displayName: string;
  email: string;
  status: 'online' | 'away' | 'offline';
  lastSeen: any;
  activeChats: number;
  maxChats: number;
  departments: string[];
}

export interface Department {
  id: string;
  name: string;
  description: string;
  agentUids: string[];
  isDefault: boolean;
}

export interface Macro {
  id: string;
  shortcut: string;
  title: string;
  text: string;
  department: string;
  createdBy: string;
}

declare global {
  interface Window {
    adventchatConfig: AdventChatConfig;
  }
}
