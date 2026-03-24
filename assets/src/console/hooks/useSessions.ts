/**
 * WP-42/43: useSessions — Real-time session listener for the operator console.
 */

import { useState, useEffect } from 'react';
import { getDb, getSiteId } from '../utils/firebase';
import type { ChatSession } from '../types';

export function useSessions() {
  const [sessions, setSessions] = useState<ChatSession[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const db = getDb();
    const siteId = getSiteId();

    const unsubscribe = db
      .collection('sessions')
      .where('siteId', '==', siteId)
      .where('status', 'in', ['waiting', 'active'])
      .orderBy('lastMessageAt', 'desc')
      .onSnapshot(
        (snapshot: any) => {
          const list: ChatSession[] = [];
          snapshot.forEach((doc: any) => {
            list.push({ id: doc.id, ...doc.data() } as ChatSession);
          });
          setSessions(list);
          setLoading(false);
        },
        (err: any) => {
          console.error('[Console] Sessions listener error:', err);
          setLoading(false);
        }
      );

    return () => unsubscribe();
  }, []);

  const waitingChats = sessions.filter(s => s.status === 'waiting');
  const activeChats = sessions.filter(s => s.status === 'active');

  return { sessions, waitingChats, activeChats, loading };
}
