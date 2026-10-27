/**
 * WP-47: useAgentStatus — Agent presence management.
 */

import { useState, useEffect, useCallback, useRef } from 'react';
import { getDb, getSiteId, serverTimestamp } from '../utils/firebase';
import type { Agent } from '../types';

// Heartbeat: refresh lastSeen every minute while online/away. The widget and mobile app treat an agent as online
// only if lastSeen is under 3 minutes old, so a closed or crashed console stops counting as online on its own.
const HEARTBEAT_MS = 60_000;
const PRESENCE_FRESH_MS = 3 * 60_000;

function isFresh(lastSeen: any): boolean {
  const ms = lastSeen?.toMillis ? lastSeen.toMillis() : 0;
  return Date.now() - ms < PRESENCE_FRESH_MS;
}

export function useAgentStatus(agentUid: string | null, displayName = '') {
  const [status, setStatus] = useState<'online' | 'away' | 'offline'>('offline');
  const [agents, setAgents] = useState<Agent[]>([]);
  const statusRef = useRef(status);
  statusRef.current = status;
  const nameRef = useRef(displayName);
  nameRef.current = displayName;

  // Listen to all agents.
  useEffect(() => {
    const db = getDb();
    const unsubscribe = db
      .collection('agents')
      .onSnapshot((snapshot: any) => {
        const list: Agent[] = [];
        snapshot.forEach((doc: any) => {
          list.push({ id: doc.id, ...doc.data() } as Agent);
        });
        setAgents(list);

        // Get current agent's status.
        if (agentUid) {
          const me = list.find(a => a.uid === agentUid);
          if (me) setStatus(me.status);
        }
      });

    return () => unsubscribe();
  }, [agentUid]);

  const updateStatus = useCallback(
    async (newStatus: 'online' | 'away' | 'offline') => {
      if (!agentUid) return;
      const db = getDb();
      const docRef = db.collection('agents').doc(agentUid);
      const doc = await docRef.get();

      // The WordPress display name is the source of truth, so a rename shows up on the next sign-in.
      const name = nameRef.current;
      if (doc.exists) {
        await docRef.update({
          status: newStatus,
          lastSeen: serverTimestamp(),
          ...(name ? { displayName: name } : {}),
        });
      } else {
        // Create agent doc if it doesn't exist.
        await docRef.set({
          uid: agentUid,
          displayName: name,
          email: '',
          status: newStatus,
          lastSeen: serverTimestamp(),
          activeChats: 0,
          maxChats: 5,
          departments: [],
        });
      }
      setStatus(newStatus);
    },
    [agentUid]
  );

  // Set online when mounting, offline when unmounting.
  useEffect(() => {
    if (!agentUid) return;
    updateStatus('online');

    const handleBeforeUnload = () => {
      updateStatus('offline');
    };
    window.addEventListener('beforeunload', handleBeforeUnload);

    return () => {
      window.removeEventListener('beforeunload', handleBeforeUnload);
      updateStatus('offline');
    };
  }, [agentUid, updateStatus]);

  // Heartbeat while the console is open (also on tab re-focus, since browsers throttle timers in background tabs).
  useEffect(() => {
    if (!agentUid) return;
    const beat = () => {
      if (statusRef.current === 'offline') return;
      getDb().collection('agents').doc(agentUid).update({ lastSeen: serverTimestamp() }).catch(() => {});
    };
    const timer = setInterval(beat, HEARTBEAT_MS);
    const onVisible = () => { if (!document.hidden) beat(); };
    document.addEventListener('visibilitychange', onVisible);
    return () => {
      clearInterval(timer);
      document.removeEventListener('visibilitychange', onVisible);
    };
  }, [agentUid]);

  const onlineAgents = agents.filter(a => a.status === 'online' && isFresh(a.lastSeen));

  return { status, agents, onlineAgents, updateStatus };
}
