/**
 * WP-47: StatusToggle — Agent online/away/offline status toggle.
 */

import React from 'react';

interface Props {
  status: 'online' | 'away' | 'offline';
  onStatusChange: (status: 'online' | 'away' | 'offline') => void;
}

const STATUS_OPTIONS: Array<{ value: 'online' | 'away' | 'offline'; label: string; color: string }> = [
  { value: 'online', label: 'Online', color: '#4ade80' },
  { value: 'away', label: 'Away', color: '#fbbf24' },
  { value: 'offline', label: 'Offline', color: '#9ca3af' },
];

export function StatusToggle({ status, onStatusChange }: Props) {
  return (
    <div className="ac-status-toggle">
      {STATUS_OPTIONS.map(opt => (
        <button
          key={opt.value}
          className={`ac-status-btn ${status === opt.value ? 'ac-status-btn--active' : ''}`}
          onClick={() => onStatusChange(opt.value)}
          title={opt.label}
        >
          <span
            className="ac-status-dot"
            style={{ backgroundColor: opt.color }}
          />
          {opt.label}
        </button>
      ))}
    </div>
  );
}
