/**
 * WP-47: useAgentStatus — Agent presence management.
 */

import { useState, useEffect, useCallback } from 'react';
import { getDb, getSiteId, serverTimestamp } from '../utils/firebase';
import type { Agent } from '../types';

export function useAgentStatus(agentUid: string | null) {
  const [status, setStatus] = useState<'online' | 'away' | 'offline'>('offline');
  const [agents, setAgents] = useState<Agent[]>([]);

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

      if (doc.exists) {
        await docRef.update({
          status: newStatus,
          lastSeen: serverTimestamp(),
        });
      } else {
        // Create agent doc if it doesn't exist.
        await docRef.set({
          uid: agentUid,
          displayName: '',
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

  const onlineAgents = agents.filter(a => a.status === 'online');

  return { status, agents, onlineAgents, updateStatus };
}
