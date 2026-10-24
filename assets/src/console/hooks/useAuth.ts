/**
 * WP-41: useAuth — Signs the operator in to Firebase.
 *
 * The console asks WordPress for a short-lived token (proving the operator's WordPress session), then exchanges it
 * for a Firebase session through the `agentSignIn` Cloud Function. No Firebase password is involved.
 */

import { useState, useEffect, useCallback } from 'react';
import { getAuth } from '../utils/firebase';
import { apiFetch } from '../utils/api';

interface AuthState {
  user: any | null;
  loading: boolean;
  error: string | null;
}

interface AgentSignInResult {
  token: string;
  displayName?: string;
}

function agentSignInUrl(): string {
  const projectId = window.adventchatConfig.firebase.projectId;
  return `https://us-central1-${projectId}.cloudfunctions.net/agentSignIn`;
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

      const tokenResp = await apiFetch<{
        success: boolean;
        data: { wpToken: string };
      }>('/operators/agent-token', { method: 'POST' });
      const wpToken = (tokenResp.data || (tokenResp as any)).wpToken;

      const res = await fetch(agentSignInUrl(), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ data: { wpToken } }),
      });
      const body = await res.json().catch(() => ({}));
      if (!res.ok) {
        throw new Error(body?.error?.message || 'Could not start your chat session.');
      }
      const { token, displayName } = body.result as AgentSignInResult;

      const userCredential = await getAuth().signInWithCustomToken(token);
      if (displayName && userCredential.user.displayName !== displayName) {
        await userCredential.user.updateProfile({ displayName }).catch(() => undefined);
      }

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
