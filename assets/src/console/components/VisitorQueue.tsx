/**
 * WP-42: VisitorQueue — Shows waiting and active chats in sidebar.
 */

import React from 'react';
import type { ChatSession } from '../types';

interface Props {
  waitingChats: ChatSession[];
  activeChats: ChatSession[];
  selectedId: string | null;
  onSelect: (session: ChatSession) => void;
  onAccept: (session: ChatSession) => void;
}

function timeAgo(ts: any): string {
  if (!ts) return '';
  const d = ts.toDate ? ts.toDate() : new Date(ts);
  const diff = Math.floor((Date.now() - d.getTime()) / 1000);
  if (diff < 60) return `${diff}s`;
  if (diff < 3600) return `${Math.floor(diff / 60)}m`;
  return `${Math.floor(diff / 3600)}h`;
}

export function VisitorQueue({ waitingChats, activeChats, selectedId, onSelect, onAccept }: Props) {
  return (
    <div className="ac-console-sidebar">
      {waitingChats.length > 0 && (
        <div className="ac-queue-section">
          <h3 className="ac-queue-title">
            Waiting <span className="ac-queue-count">{waitingChats.length}</span>
          </h3>
          {waitingChats.map(chat => (
            <div
              key={chat.id}
              className={`ac-queue-item ac-queue-item--waiting ${selectedId === chat.id ? 'ac-queue-item--active' : ''}`}
              onClick={() => onSelect(chat)}
            >
              <div className="ac-queue-item__name">{chat.visitorName || 'Visitor'}</div>
              <div className="ac-queue-item__meta">
                {chat.visitorInfo?.pageTitle || chat.visitorInfo?.pageUrl || 'Unknown page'}
              </div>
              <div className="ac-queue-item__time">{timeAgo(chat.startedAt)}</div>
              <button
                className="ac-queue-item__accept"
                onClick={(e) => { e.stopPropagation(); onAccept(chat); }}
              >
                Accept
              </button>
            </div>
          ))}
        </div>
      )}

      <div className="ac-queue-section">
        <h3 className="ac-queue-title">
          Active <span className="ac-queue-count">{activeChats.length}</span>
        </h3>
        {activeChats.length === 0 && (
          <p className="ac-queue-empty">No active chats</p>
        )}
        {activeChats.map(chat => (
          <div
            key={chat.id}
            className={`ac-queue-item ${selectedId === chat.id ? 'ac-queue-item--active' : ''}`}
            onClick={() => onSelect(chat)}
          >
            <div className="ac-queue-item__name">{chat.visitorName || 'Visitor'}</div>
            <div className="ac-queue-item__meta">{chat.department || 'General'}</div>
            <div className="ac-queue-item__time">{timeAgo(chat.lastMessageAt)}</div>
          </div>
        ))}
      </div>
    </div>
  );
}
