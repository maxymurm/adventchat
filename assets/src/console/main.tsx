/**
 * AdventChat Operator Console — React SPA entry.
 *
 * Implements WP-40 through WP-52.
 */

import React, { useState, useCallback, useEffect } from 'react';
import { createRoot } from 'react-dom/client';

import { initFirebase, getDb, serverTimestamp } from './utils/firebase';
import { useAuth } from './hooks/useAuth';
import { useSessions } from './hooks/useSessions';
import { useMessages } from './hooks/useMessages';
import { useAgentStatus } from './hooks/useAgentStatus';
import { useTyping } from './hooks/useTyping';
import { useMacros } from './hooks/useMacros';

import { VisitorQueue } from './components/VisitorQueue';
import { ChatWindow } from './components/ChatWindow';
import { VisitorSidebar } from './components/VisitorSidebar';
import { StatusToggle } from './components/StatusToggle';
import { TransferDialog } from './components/TransferDialog';

import type { ChatSession, Department } from './types';

import './console.css';

// Initialize Firebase.
initFirebase();

function App() {
  const { user, loading: authLoading, error: authError } = useAuth();
  const agentUid = user?.uid || '';
  const agentName = user?.displayName || 'Agent';
  const agentPhotoUrl = user?.photoURL || '';

  // Fullscreen
  const [isFullscreen, setIsFullscreen] = useState(false);
  useEffect(() => {
    const onFsChange = () => setIsFullscreen(!!document.fullscreenElement);
    document.addEventListener('fullscreenchange', onFsChange);
    return () => document.removeEventListener('fullscreenchange', onFsChange);
  }, []);
  const toggleFullscreen = useCallback(() => {
    const el = document.getElementById('adventchat-console-root');
    if (!el) return;
    if (!document.fullscreenElement) {
      el.requestFullscreen().catch(() => {});
    } else {
      document.exitFullscreen().catch(() => {});
    }
  }, []);

  const { waitingChats, activeChats, loading: sessionsLoading } = useSessions(!!user);
  const { status, agents, updateStatus } = useAgentStatus(agentUid || null);

  const [selectedSession, setSelectedSession] = useState<ChatSession | null>(null);
  const [showTransfer, setShowTransfer] = useState(false);
  const [departments] = useState<Department[]>([]); // Loaded from Firestore in later phase.

  const { messages, sendMessage } = useMessages(
    selectedSession?.id || null,
    agentUid
  );

  const { visitorTyping, visitorPreview, onAgentInput } = useTyping(
    selectedSession?.id || null,
    agentUid,
    agentName
  );

  const { macros, findByShortcut } = useMacros();

  // WP-48: Accept a waiting chat (round-robin / manual assignment).
  const acceptChat = useCallback(
    async (session: ChatSession) => {
      const db = getDb();
      await db.collection('sessions').doc(session.id).update({
        status: 'active',
        agentUid: agentUid,
        agentName: agentName,
        ...(agentPhotoUrl ? { agentPhotoUrl } : {}),
      });

      // Add system message — best-effort.
      try {
        await db
          .collection('sessions')
          .doc(session.id)
          .collection('messages')
          .add({
            senderUid: agentUid || 'system',
            senderName: 'System',
            senderType: 'system',
            text: `${agentName} joined the chat.`,
            timestamp: serverTimestamp(),
            readByAgent: true,
            readByVisitor: true,
          });
      } catch (_) { /* ignore */ }

      setSelectedSession({ ...session, status: 'active', agentUid, agentName });
    },
    [agentUid, agentName, agentPhotoUrl]
  );

  // WP-43: End chat.
  const endChat = useCallback(async () => {
    if (!selectedSession) return;
    const db = getDb();

    // Update status first so the visitor sees the chat end immediately.
    await db.collection('sessions').doc(selectedSession.id).update({
      status: 'ended',
      endedAt: serverTimestamp(),
    });

    // System message is best-effort — don't let a rules error block the end.
    try {
      await db
        .collection('sessions')
        .doc(selectedSession.id)
        .collection('messages')
        .add({
          senderUid: agentUid || 'system',
          senderName: 'System',
          senderType: 'system',
          text: 'Chat ended by agent.',
          timestamp: serverTimestamp(),
          readByAgent: true,
          readByVisitor: true,
        });
    } catch (_) { /* ignore */ }

    setSelectedSession(null);
  }, [selectedSession, agentUid]);

  // WP-50: Transfer chat.
  const transferChat = useCallback(
    async (targetUid: string, targetName: string, department?: string) => {
      if (!selectedSession) return;
      const db = getDb();

      const updates: Record<string, any> = {};
      if (targetUid) {
        updates.agentUid = targetUid;
        updates.agentName = targetName;
      }
      if (department) {
        updates.department = department;
        updates.status = 'waiting';
        updates.agentUid = '';
        updates.agentName = '';
      }

      await db.collection('sessions').doc(selectedSession.id).update(updates);

      const msg = targetUid
        ? `Chat transferred to ${targetName}.`
        : `Chat transferred to ${department} department.`;

      await db
        .collection('sessions')
        .doc(selectedSession.id)
        .collection('messages')
        .add({
          senderUid: 'system',
          senderName: 'System',
          senderType: 'system',
          text: msg,
          timestamp: serverTimestamp(),
          readByAgent: true,
          readByVisitor: true,
        });

      setShowTransfer(false);
      setSelectedSession(null);
    },
    [selectedSession]
  );

  // WP-51: Send with isNote support.
  const handleSend = useCallback(
    (text: string, isNote = false) => {
      sendMessage(text, agentName, isNote);
    },
    [sendMessage, agentName]
  );

  // Loading / error states.
  if (authLoading) {
    return (
      <div className="ac-console ac-console--loading">
        <div className="ac-console__spinner" />
        <p>Connecting to Firebase...</p>
      </div>
    );
  }

  if (authError) {
    return (
      <div className="ac-console ac-console--error">
        <h3>Authentication Error</h3>
        <p>{authError}</p>
        <p>Please check your Firebase configuration in AdventChat Settings.</p>
      </div>
    );
  }

  return (
    <div className="ac-console">
      {/* Top bar */}
      <div className="ac-console__topbar">
        <h2 className="ac-console__logo">AdventChat</h2>
        <div className="ac-console__topbar-right">
          <StatusToggle status={status} onStatusChange={updateStatus} />
          <span className="ac-console__agent-name">{agentName}</span>
          <button
            className={`ac-console__fullscreen-btn${isFullscreen ? ' ac-console__fullscreen-btn--active' : ''}`}
            onClick={toggleFullscreen}
            title={isFullscreen ? 'Exit full screen (Esc)' : 'Full screen'}
            aria-label={isFullscreen ? 'Exit full screen' : 'Enter full screen'}
          >
            {isFullscreen ? (
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M5 1v4H1M11 1v4h4M5 15v-4H1M11 15v-4h4" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            ) : (
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M1 5V1h4M15 5V1h-4M1 11v4h4M15 11v4h-4" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            )}
          </button>
        </div>
      </div>

      {/* Main layout */}
      <div className="ac-console__main">
        {/* Left sidebar — queue */}
        <VisitorQueue
          waitingChats={waitingChats}
          activeChats={activeChats}
          selectedId={selectedSession?.id || null}
          onSelect={(s) => setSelectedSession(s)}
          onAccept={acceptChat}
        />

        {/* Center — chat */}
        <ChatWindow
          session={selectedSession}
          messages={messages}
          agentUid={agentUid}
          visitorTyping={visitorTyping}
          visitorPreview={visitorPreview}
          macros={macros}
          onSend={handleSend}
          onInput={onAgentInput}
          onEndChat={endChat}
          onTransfer={() => setShowTransfer(true)}
          findMacro={findByShortcut}
        />

        {/* Right sidebar — visitor info */}
        <VisitorSidebar session={selectedSession} />
      </div>

      {/* Transfer dialog */}
      {showTransfer && (
        <TransferDialog
          agents={agents}
          departments={departments}
          onTransfer={transferChat}
          onClose={() => setShowTransfer(false)}
        />
      )}
    </div>
  );
}

const container = document.getElementById('adventchat-console-root');
if (container) {
  createRoot(container).render(<App />);
}
