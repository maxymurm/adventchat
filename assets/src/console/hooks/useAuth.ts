/**
 * WP-41: useAuth — Handles operator Firebase sign-in via WP REST API.
 */

import { useState, useEffect, useCallback } from 'react';
import { getAuth } from '../utils/firebase';
import { apiFetch } from '../utils/api';

declare const firebase: any;

interface AuthState {
  user: any | null;
  loading: boolean;
  error: string | null;
}

export function useAuth() {
  const [state, setState] = useState<AuthState>({
    user: null,
    loading: true,
    error: null,
  });

  const signIn = useCallback(async () => {
    try {
      setState(s => ({ ...s, loading: true, error: null }));

      // First sync the operator (creates Firebase user if needed).
      await apiFetch('/operators/sync', { method: 'POST' });

      // Get Firebase credentials.
      const tokenResp = await apiFetch<{
        success: boolean;
        data: { idToken: string; firebase_uid: string; email: string; password: string };
      }>('/operators/token', { method: 'POST' });

      const tokenData = tokenResp.data || tokenResp;
      const auth = getAuth();

      // Sign in to Firebase client SDK with email + password.
      const userCredential = await auth.signInWithEmailAndPassword(
        tokenData.email,
        tokenData.password
      );

      setState({
        user: userCredential.user,
        loading: false,
        error: null,
      });
    } catch (err: any) {
      setState({
        user: null,
        loading: false,
        error: err.message || 'Authentication failed',
      });
    }
  }, []);

  useEffect(() => {
    signIn();
  }, [signIn]);

  return { ...state, signIn };
}
