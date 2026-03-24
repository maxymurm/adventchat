/**
 * WP-35/43: useTyping — Typing indicator for agent side.
 */

import { useState, useEffect, useCallback, useRef } from 'react';
import { getDb, serverTimestamp } from '../utils/firebase';

export function useTyping(sessionId: string | null, agentUid: string, agentName: string) {
  const [visitorTyping, setVisitorTyping] = useState(false);
  const timeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  // Listen for visitor typing.
  useEffect(() => {
    if (!sessionId) return;
    const db = getDb();
    const unsubscribe = db
      .collection('sessions')
      .doc(sessionId)
      .collection('typing')
      .onSnapshot((snapshot: any) => {
        let typing = false;
        snapshot.forEach((doc: any) => {
          const data = doc.data();
          if (doc.id !== agentUid && data.isTyping) {
            typing = true;
          }
        });
        setVisitorTyping(typing);
      });

    return () => unsubscribe();
  }, [sessionId, agentUid]);

  // Set agent typing.
  const setAgentTyping = useCallback(
    (isTyping: boolean) => {
      if (!sessionId) return;
      const db = getDb();
      db.collection('sessions')
        .doc(sessionId)
        .collection('typing')
        .doc(agentUid)
        .set({
          isTyping,
          name: agentName,
          timestamp: serverTimestamp(),
        })
        .catch(() => {});
    },
    [sessionId, agentUid, agentName]
  );

  const onAgentInput = useCallback(() => {
    setAgentTyping(true);
    if (timeoutRef.current) clearTimeout(timeoutRef.current);
    timeoutRef.current = setTimeout(() => setAgentTyping(false), 2000);
  }, [setAgentTyping]);

  return { visitorTyping, setAgentTyping, onAgentInput };
}
