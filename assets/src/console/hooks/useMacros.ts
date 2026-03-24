/**
 * WP-46: useMacros — Canned responses/macros management.
 */

import { useState, useEffect, useCallback } from 'react';
import { getDb, serverTimestamp } from '../utils/firebase';
import type { Macro } from '../types';

export function useMacros() {
  const [macros, setMacros] = useState<Macro[]>([]);

  useEffect(() => {
    const db = getDb();
    const unsubscribe = db
      .collection('macros')
      .orderBy('shortcut', 'asc')
      .onSnapshot((snapshot: any) => {
        const list: Macro[] = [];
        snapshot.forEach((doc: any) => {
          list.push({ id: doc.id, ...doc.data() } as Macro);
        });
        setMacros(list);
      });

    return () => unsubscribe();
  }, []);

  const addMacro = useCallback(
    async (macro: Omit<Macro, 'id'>) => {
      const db = getDb();
      await db.collection('macros').add({ ...macro, createdAt: serverTimestamp() });
    },
    []
  );

  const updateMacro = useCallback(
    async (id: string, data: Partial<Macro>) => {
      const db = getDb();
      await db.collection('macros').doc(id).update(data);
    },
    []
  );

  const deleteMacro = useCallback(
    async (id: string) => {
      const db = getDb();
      await db.collection('macros').doc(id).delete();
    },
    []
  );

  const findByShortcut = useCallback(
    (input: string): Macro | undefined => {
      if (!input.startsWith('/')) return undefined;
      const cmd = input.slice(1).toLowerCase();
      return macros.find(m => m.shortcut.toLowerCase() === cmd);
    },
    [macros]
  );

  return { macros, addMacro, updateMacro, deleteMacro, findByShortcut };
}
