/**
 * WP-35/43: useTyping — Typing indicator for agent side.
 */

import { useState, useEffect, useCallback, useRef } from 'react';
import { getDb, serverTimestamp } from '../utils/firebase';

export function useTyping(sessionId: string | null, agentUid: string, agentName: string) {
  const [visitorTyping, setVisitorTyping] = useState(false);
  const [visitorPreview, setVisitorPreview] = useState('');
  const timeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  // Listen for visitor typing.
  useEffect(() => {
    if (!sessionId) return;
    // eslint-disable-next-line no-console
    console.log('[AdventChat] useTyping subscribed to session', sessionId);
    const db = getDb();
    const unsubscribe = db
      .collection('sessions')
      .doc(sessionId)
      .collection('typing')
      .onSnapshot(
        (snapshot: any) => {
          let typing = false;
          let preview = '';
          snapshot.forEach((doc: any) => {
            const data = doc.data();
            // Filter by role rather than UID — visitor + agent tabs may share
            // the same anonymous Firebase auth UID in the same browser.
            const isVisitorDoc = data.role ? data.role === 'visitor' : doc.id !== agentUid;
            // eslint-disable-next-line no-console
            console.log(
              '[AdventChat] typing doc',
              doc.id,
              'role=', data.role,
              'isVisitorDoc=', isVisitorDoc,
              'isTyping=', data.isTyping,
              'previewText=', JSON.stringify(data.previewText)
            );
            if (isVisitorDoc && data.isTyping) {
              typing = true;
              if (typeof data.previewText === 'string') {
                preview = data.previewText;
              }
            }
          });
          // eslint-disable-next-line no-console
          console.log('[AdventChat] resolved visitorTyping=', typing, 'preview=', JSON.stringify(preview));
          setVisitorTyping(typing);
          setVisitorPreview(typing ? preview : '');
        },
        (err: any) => {
          // eslint-disable-next-line no-console
          console.error('[AdventChat] typing snapshot error', err);
        }
      );

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
          role: 'agent',
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

  return { visitorTyping, visitorPreview, setAgentTyping, onAgentInput };
}
