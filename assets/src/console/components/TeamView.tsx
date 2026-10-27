/**
 * TeamView — Agent management and monitoring: who is online, how loaded they are, and how they handle chats.
 *
 * Presence is "effective": an agent who says online/away but whose heartbeat is older than 3 minutes is shown as
 * offline (closed tab, lost connection), matching what visitors see on the widget.
 */

import React, { useEffect, useMemo, useState } from 'react';
import type { Agent, ChatSession } from '../types';

type Range = 'today' | '7d' | '30d';
type Presence = 'online' | 'away' | 'offline';

const FRESH_MS = 3 * 60_000;
const DEFAULT_CAPACITY = 5;

const RANGES: Array<{ value: Range; label: string }> = [
  { value: 'today', label: 'Today' },
  { value: '7d', label: '7 days' },
  { value: '30d', label: '30 days' },
];

function ms(ts: any): number {
  if (!ts) return 0;
  if (typeof ts.toMillis === 'function') return ts.toMillis();
  if (typeof ts.toDate === 'function') return ts.toDate().getTime();
  return new Date(ts).getTime() || 0;
}

function rangeStart(range: Range): number {
  if (range === 'today') {
    const d = new Date();
    d.setHours(0, 0, 0, 0);
    return d.getTime();
  }
  return Date.now() - (range === '7d' ? 7 : 30) * 24 * 60 * 60 * 1000;
}

function duration(sec: number | null): string {
  if (sec === null) return '—';
  if (sec < 60) return `${Math.round(sec)}s`;
  if (sec < 3600) return `${Math.floor(sec / 60)}m ${Math.round(sec % 60)}s`;
  return `${Math.floor(sec / 3600)}h ${Math.round((sec % 3600) / 60)}m`;
}

function ago(timestampMs: number): string {
  if (!timestampMs) return 'never';
  const sec = Math.max(0, (Date.now() - timestampMs) / 1000);
  if (sec < 60) return 'just now';
  if (sec < 3600) return `${Math.floor(sec / 60)} min ago`;
  if (sec < 86400) return `${Math.floor(sec / 3600)} h ago`;
  return `${Math.floor(sec / 86400)} d ago`;
}

function mean(values: number[]): number | null {
  return values.length ? values.reduce((a, b) => a + b, 0) / values.length : null;
}

function presenceOf(agent: Agent, now: number): Presence {
  const fresh = now - ms(agent.lastSeen) < FRESH_MS;
  return fresh && (agent.status === 'online' || agent.status === 'away') ? agent.status : 'offline';
}

function wpUserId(agentId: string): number | null {
  const m = /^wp-(\d+)$/.exec(agentId);
  return m ? Number(m[1]) : null;
}

interface Props {
  agents: Agent[];
  sessions: ChatSession[];
  loading: boolean;
  myUid: string;
}

export function TeamView({ agents, sessions, loading, myUid }: Props) {
  const [range, setRange] = useState<Range>('7d');
  const [now, setNow] = useState(Date.now());

  // Staleness and waiting times are time-based, so re-evaluate even when nothing changes in Firestore.
  useEffect(() => {
    const t = setInterval(() => setNow(Date.now()), 15_000);
    return () => clearInterval(t);
  }, []);

  const data = useMemo(() => {
    const from = rangeStart(range);
    const inRange = sessions.filter(s => ms(s.startedAt) >= from);

    const roster = agents
      .filter(a => a.id.startsWith('wp-'))
      .map(a => {
        const mine = inRange.filter(s => s.agentUid === a.id);
        const responses = mine
          .map(s => (ms((s as any).firstResponseAt) && ms(s.startedAt) ? (ms((s as any).firstResponseAt) - ms(s.startedAt)) / 1000 : null))
          .filter((v): v is number => v !== null && v >= 0);
        const handle = mine
          .filter(s => s.status === 'ended' && ms((s as any).endedAt) && ms(s.startedAt))
          .map(s => (ms((s as any).endedAt) - ms(s.startedAt)) / 1000)
          .filter(v => v >= 0);
        const rated = mine.filter(s => s.rating > 0);
        const active = sessions.filter(s => s.status === 'active' && s.agentUid === a.id).length;
        return {
          agent: a,
          presence: presenceOf(a, now),
          active,
          capacity: a.maxChats || DEFAULT_CAPACITY,
          handled: mine.length,
          firstResponse: mean(responses),
          handleTime: mean(handle),
          rating: mean(rated.map(s => s.rating)),
          ratings: rated.length,
        };
      });

    const order: Record<Presence, number> = { online: 0, away: 1, offline: 2 };
    roster.sort((x, y) => order[x.presence] - order[y.presence] || y.handled - x.handled || (x.agent.displayName || '').localeCompare(y.agent.displayName || ''));

    const waiting = sessions.filter(s => s.status === 'waiting');
    const oldestWait = waiting.length ? Math.max(...waiting.map(s => (now - ms(s.startedAt)) / 1000)) : null;
    const allResponses = roster.flatMap(r => (r.firstResponse === null ? [] : [r.firstResponse]));
    const rated = inRange.filter(s => s.rating > 0);
    const missed = inRange.filter(s => s.status === 'ended' && !s.agentUid).length;

    return {
      roster,
      online: roster.filter(r => r.presence === 'online').length,
      away: roster.filter(r => r.presence === 'away').length,
      offline: roster.filter(r => r.presence === 'offline').length,
      waiting: waiting.length,
      oldestWait,
      active: sessions.filter(s => s.status === 'active').length,
      chats: inRange.length,
      missed,
      firstResponse: mean(allResponses),
      rating: mean(rated.map(s => s.rating)),
      ratings: rated.length,
    };
  }, [agents, sessions, range, now]);

  const cards: Array<{ label: string; value: string; hint?: string; tone?: string }> = [
    { label: 'Online', value: String(data.online), hint: `${data.away} away · ${data.offline} offline`, tone: data.online ? 'good' : 'bad' },
    { label: 'Waiting now', value: String(data.waiting), hint: data.oldestWait !== null ? `longest ${duration(data.oldestWait)}` : 'queue is clear', tone: data.waiting ? 'warn' : '' },
    { label: 'Active chats', value: String(data.active) },
    { label: 'Chats', value: String(data.chats), hint: data.missed ? `${data.missed} missed` : 'none missed' },
    { label: 'Avg first response', value: duration(data.firstResponse), hint: 'time to first agent reply' },
    { label: 'Avg rating', value: data.rating === null ? '—' : `${data.rating.toFixed(1)} ★`, hint: `${data.ratings} rating${data.ratings === 1 ? '' : 's'}` },
  ];

  return (
    <div className="ac-team">
      <div className="ac-team__head">
        <div>
          <h3>Team</h3>
          <p>Live availability and performance for everyone who handles chats.</p>
        </div>
        <div className="ac-team__tools">
          <div className="ac-team__range" role="group" aria-label="Report period">
            {RANGES.map(r => (
              <button key={r.value} type="button" className={range === r.value ? 'is-active' : ''} onClick={() => setRange(r.value)}>
                {r.label}
              </button>
            ))}
          </div>
          <a className="ac-team__link" href="users.php">Manage users &amp; roles</a>
        </div>
      </div>

      <div className="ac-team__cards">
        {cards.map(c => (
          <div key={c.label} className={`ac-team__card${c.tone ? ` ac-team__card--${c.tone}` : ''}`}>
            <span className="ac-team__card-label">{c.label}</span>
            <strong>{c.value}</strong>
            {c.hint && <small>{c.hint}</small>}
          </div>
        ))}
      </div>

      <div className="ac-team__table-wrap">
        <table className="ac-team__table">
          <thead>
            <tr>
              <th>Agent</th>
              <th>Status</th>
              <th>Load</th>
              <th>Chats handled</th>
              <th>First response</th>
              <th>Avg handle time</th>
              <th>Rating</th>
              <th>Last seen</th>
            </tr>
          </thead>
          <tbody>
            {data.roster.map(r => {
              const wpId = wpUserId(r.agent.id);
              const name = r.agent.displayName || `User #${wpId ?? r.agent.id}`;
              const pct = Math.min(100, Math.round((r.active / r.capacity) * 100));
              return (
                <tr key={r.agent.id}>
                  <td>
                    <div className="ac-team__who">
                      <span className="ac-team__avatar">{name.trim().charAt(0).toUpperCase()}</span>
                      <div>
                        {wpId ? <a href={`user-edit.php?user_id=${wpId}`} title="Edit name, email and role in WordPress">{name}</a> : name}
                        {r.agent.id === myUid && <em> (you)</em>}
                      </div>
                    </div>
                  </td>
                  <td>
                    <span className={`ac-team__pill ac-team__pill--${r.presence}`}>
                      <i />
                      {r.presence === 'online' ? 'Online' : r.presence === 'away' ? 'Away' : 'Offline'}
                    </span>
                  </td>
                  <td>
                    <div className="ac-team__load" title={`${r.active} of ${r.capacity} concurrent chats`}>
                      <div className="ac-team__bar"><span style={{ width: `${pct}%` }} className={pct >= 100 ? 'is-full' : ''} /></div>
                      <small>{r.active}/{r.capacity}</small>
                    </div>
                  </td>
                  <td>{r.handled}</td>
                  <td>{duration(r.firstResponse)}</td>
                  <td>{duration(r.handleTime)}</td>
                  <td>{r.rating === null ? '—' : <>{r.rating.toFixed(1)} ★ <small>({r.ratings})</small></>}</td>
                  <td>{ago(ms(r.agent.lastSeen))}</td>
                </tr>
              );
            })}
            {!data.roster.length && (
              <tr>
                <td colSpan={8} className="ac-team__empty">{loading ? 'Loading…' : 'No agents have signed in to chat yet.'}</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
      <p className="ac-team__note">
        Agents appear after their first sign-in. An agent is shown offline if their app or browser has not checked in for
        3 minutes. To change a name, email or role, open the agent&apos;s WordPress profile; the new name is used the next
        time they sign in to chat.
      </p>
    </div>
  );
}
