/**
 * WP-50: TransferDialog — Transfer chat to another agent or department.
 */

import React, { useState } from 'react';
import type { Agent, Department } from '../types';

interface Props {
  agents: Agent[];
  departments: Department[];
  onTransfer: (targetUid: string, targetName: string, department?: string) => void;
  onClose: () => void;
}

export function TransferDialog({ agents, departments, onTransfer, onClose }: Props) {
  const [tab, setTab] = useState<'agent' | 'department'>('agent');
  const [selectedAgent, setSelectedAgent] = useState('');
  const [selectedDept, setSelectedDept] = useState('');

  const onlineAgents = agents.filter(a => a.status === 'online');

  const handleTransfer = () => {
    if (tab === 'agent' && selectedAgent) {
      const agent = agents.find(a => a.uid === selectedAgent);
      if (agent) onTransfer(agent.uid, agent.displayName);
    } else if (tab === 'department' && selectedDept) {
      const dept = departments.find(d => d.id === selectedDept);
      if (dept) onTransfer('', '', dept.name);
    }
  };

  return (
    <div className="ac-dialog-overlay" onClick={onClose}>
      <div className="ac-dialog" onClick={e => e.stopPropagation()}>
        <div className="ac-dialog__header">
          <h3>Transfer Chat</h3>
          <button className="ac-dialog__close" onClick={onClose}>×</button>
        </div>

        <div className="ac-dialog__tabs">
          <button
            className={`ac-dialog__tab ${tab === 'agent' ? 'ac-dialog__tab--active' : ''}`}
            onClick={() => setTab('agent')}
          >
            Agent
          </button>
          <button
            className={`ac-dialog__tab ${tab === 'department' ? 'ac-dialog__tab--active' : ''}`}
            onClick={() => setTab('department')}
          >
            Department
          </button>
        </div>

        <div className="ac-dialog__body">
          {tab === 'agent' && (
            <div className="ac-transfer-list">
              {onlineAgents.length === 0 && <p>No online agents available.</p>}
              {onlineAgents.map(agent => (
                <label key={agent.uid} className="ac-transfer-option">
                  <input
                    type="radio"
                    name="transfer-agent"
                    value={agent.uid}
                    checked={selectedAgent === agent.uid}
                    onChange={() => setSelectedAgent(agent.uid)}
                  />
                  <span>{agent.displayName || agent.email}</span>
                  <span className="ac-transfer-chats">
                    {agent.activeChats}/{agent.maxChats} chats
                  </span>
                </label>
              ))}
            </div>
          )}

          {tab === 'department' && (
            <div className="ac-transfer-list">
              {departments.length === 0 && <p>No departments configured.</p>}
              {departments.map(dept => (
                <label key={dept.id} className="ac-transfer-option">
                  <input
                    type="radio"
                    name="transfer-dept"
                    value={dept.id}
                    checked={selectedDept === dept.id}
                    onChange={() => setSelectedDept(dept.id)}
                  />
                  <span>{dept.name}</span>
                  <span className="ac-transfer-chats">{dept.description}</span>
                </label>
              ))}
            </div>
          )}
        </div>

        <div className="ac-dialog__footer">
          <button className="ac-btn" onClick={onClose}>Cancel</button>
          <button className="ac-btn ac-btn--primary" onClick={handleTransfer}>
            Transfer
          </button>
        </div>
      </div>
    </div>
  );
}
