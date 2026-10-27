/**
 * useTeamSessions — Sessions from the last 30 days (any status) for team monitoring.
 *
 * Uses a single-field range query so no composite index is needed; the site filter is applied client-side.
 */

import { useState, useEffect } from 'react';
import { getDb, getSiteId } from '../utils/firebase';
import type { ChatSession } from '../types';

export const TEAM_WINDOW_DAYS = 30;

export function useTeamSessions(enabled: boolean) {
  const [sessions, setSessions] = useState<ChatSession[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!enabled) return;
    const since = new Date(Date.now() - TEAM_WINDOW_DAYS * 24 * 60 * 60 * 1000);
    const siteId = getSiteId();

    const unsubscribe = getDb()
      .collection('sessions')
      .where('startedAt', '>=', since)
      .onSnapshot(
        (snapshot: any) => {
          const list: ChatSession[] = [];
          snapshot.forEach((doc: any) => {
            const session = { id: doc.id, ...doc.data() } as ChatSession;
            if (!session.siteId || session.siteId === siteId) list.push(session);
          });
          setSessions(list);
          setLoading(false);
        },
        (err: any) => {
          console.error('[Console] Team sessions listener error:', err);
          setLoading(false);
        }
      );

    return () => unsubscribe();
  }, [enabled]);

  return { sessions, loading };
}
