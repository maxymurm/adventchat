/**
 * WP-43: useMessages — Real-time messages for a specific chat session.
 */

import { useState, useEffect, useCallback } from 'react';
import { getDb, serverTimestamp, increment } from '../utils/firebase';
import type { ChatMessage } from '../types';

export function useMessages(sessionId: string | null, agentUid: string) {
  const [messages, setMessages] = useState<ChatMessage[]>([]);

  useEffect(() => {
    if (!sessionId) {
      setMessages([]);
      return;
    }

    const db = getDb();
    const unsubscribe = db
      .collection('sessions')
      .doc(sessionId)
      .collection('messages')
      .orderBy('timestamp', 'asc')
      .onSnapshot((snapshot: any) => {
        const list: ChatMessage[] = [];
        snapshot.forEach((doc: any) => {
          list.push({ id: doc.id, ...doc.data() } as ChatMessage);
        });
        setMessages(list);

        // Mark agent-unread messages as read.
        snapshot.docChanges().forEach((change: any) => {
          if (change.type === 'added') {
            const data = change.doc.data();
            if (data.senderType === 'visitor' && !data.readByAgent) {
              change.doc.ref.update({ readByAgent: true });
            }
          }
        });
      });

    return () => unsubscribe();
  }, [sessionId, agentUid]);

  const sendMessage = useCallback(
    async (text: string, agentName: string, isNote = false) => {
      if (!sessionId || !text.trim()) return;
      const db = getDb();

      await db
        .collection('sessions')
        .doc(sessionId)
        .collection('messages')
        .add({
          senderUid: agentUid,
          senderName: agentName,
          senderType: isNote ? 'system' : 'agent',
          text: text.trim(),
          timestamp: serverTimestamp(),
          readByAgent: true,
          readByVisitor: false,
          isNote: isNote,
        });

      await db.collection('sessions').doc(sessionId).update({
        lastMessageAt: serverTimestamp(),
        messageCount: increment(1),
      });
    },
    [sessionId, agentUid]
  );

  return { messages, sendMessage };
}
