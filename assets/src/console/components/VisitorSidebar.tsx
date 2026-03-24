/**
 * WP-44: VisitorSidebar — Visitor context panel (info, browser, page, etc).
 */

import React from 'react';
import type { ChatSession } from '../types';

interface Props {
  session: ChatSession | null;
}

export function VisitorSidebar({ session }: Props) {
  if (!session) return null;

  const info = session.visitorInfo || {};
  const startTime = session.startedAt?.toDate
    ? session.startedAt.toDate().toLocaleString()
    : '';

  return (
    <div className="ac-visitor-sidebar">
      <h3 className="ac-visitor-sidebar__title">Visitor Info</h3>

      <div className="ac-visitor-sidebar__section">
        <div className="ac-info-row">
          <span className="ac-info-label">Name</span>
          <span className="ac-info-value">{session.visitorName || 'Anonymous'}</span>
        </div>
        {session.visitorEmail && (
          <div className="ac-info-row">
            <span className="ac-info-label">Email</span>
            <span className="ac-info-value">{session.visitorEmail}</span>
          </div>
        )}
        <div className="ac-info-row">
          <span className="ac-info-label">Status</span>
          <span className={`ac-info-badge ac-info-badge--${session.status}`}>
            {session.status}
          </span>
        </div>
        {session.department && (
          <div className="ac-info-row">
            <span className="ac-info-label">Department</span>
            <span className="ac-info-value">{session.department}</span>
          </div>
        )}
      </div>

      <h4 className="ac-visitor-sidebar__subtitle">Browser & Device</h4>
      <div className="ac-visitor-sidebar__section">
        <div className="ac-info-row">
          <span className="ac-info-label">Page</span>
          <span className="ac-info-value ac-info-value--truncate" title={info.pageUrl}>
            {info.pageTitle || info.pageUrl || '—'}
          </span>
        </div>
        {info.referrer && (
          <div className="ac-info-row">
            <span className="ac-info-label">Referrer</span>
            <span className="ac-info-value ac-info-value--truncate" title={info.referrer}>
              {info.referrer}
            </span>
          </div>
        )}
        <div className="ac-info-row">
          <span className="ac-info-label">Screen</span>
          <span className="ac-info-value">
            {info.screenWidth && info.screenHeight
              ? `${info.screenWidth}×${info.screenHeight}`
              : '—'}
          </span>
        </div>
        <div className="ac-info-row">
          <span className="ac-info-label">Language</span>
          <span className="ac-info-value">{info.language || '—'}</span>
        </div>
        <div className="ac-info-row">
          <span className="ac-info-label">Timezone</span>
          <span className="ac-info-value">{info.timezone || '—'}</span>
        </div>
      </div>

      <h4 className="ac-visitor-sidebar__subtitle">Session</h4>
      <div className="ac-visitor-sidebar__section">
        <div className="ac-info-row">
          <span className="ac-info-label">Started</span>
          <span className="ac-info-value">{startTime}</span>
        </div>
        <div className="ac-info-row">
          <span className="ac-info-label">Messages</span>
          <span className="ac-info-value">{session.messageCount}</span>
        </div>
        {session.rating > 0 && (
          <div className="ac-info-row">
            <span className="ac-info-label">Rating</span>
            <span className="ac-info-value">{'★'.repeat(session.rating)}{'☆'.repeat(5 - session.rating)}</span>
          </div>
        )}
      </div>
    </div>
  );
}
