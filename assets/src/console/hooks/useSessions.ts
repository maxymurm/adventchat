/**
 * WP-42/43: useSessions — Real-time session listener for the operator console.
 */

import { useState, useEffect } from 'react';
import { getDb, getSiteId } from '../utils/firebase';
import type { ChatSession } from '../types';

export function useSessions(ready: boolean) {
  const [sessions, setSessions] = useState<ChatSession[]>([]);
  const [loading, setLoading] = useState(true);

  function toTime(ts: any): number {
    if (!ts) return 0;
    if (typeof ts.toDate === 'function') return ts.toDate().getTime();
    return new Date(ts).getTime() || 0;
  }

  useEffect(() => {
    if (!ready) return;

    const db = getDb();
    const siteId = getSiteId();

    const unsubscribe = db
      .collection('sessions')
      .where('siteId', '==', siteId)
      .onSnapshot(
        (snapshot: any) => {
          const list: ChatSession[] = [];
          snapshot.forEach((doc: any) => {
            const session = { id: doc.id, ...doc.data() } as ChatSession;
            if (session.status === 'waiting' || session.status === 'active') {
              list.push(session);
            }
          });
          list.sort((a, b) => toTime(b.lastMessageAt) - toTime(a.lastMessageAt));
          setSessions(list);
          setLoading(false);
        },
        (err: any) => {
          console.error('[Console] Sessions listener error:', err);
          setLoading(false);
        }
      );

    return () => unsubscribe();
  }, [ready]);

  const waitingChats = sessions.filter(s => s.status === 'waiting');
  const activeChats = sessions.filter(s => s.status === 'active');

  return { sessions, waitingChats, activeChats, loading };
}
