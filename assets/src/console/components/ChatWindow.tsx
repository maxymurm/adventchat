/**
 * WP-43: ChatWindow — WhatsApp-style active chat window.
 */

import React, { useState, useRef, useEffect } from 'react';
import type { ChatMessage, ChatSession, Macro } from '../types';

interface Props {
  session: ChatSession | null;
  messages: ChatMessage[];
  agentUid: string;
  visitorTyping: boolean;
  macros: Macro[];
  onSend: (text: string, isNote?: boolean) => void;
  onInput: () => void;
  onEndChat: () => void;
  onTransfer: () => void;
  findMacro: (input: string) => Macro | undefined;
}

function formatTime(ts: any): string {
  if (!ts) return '';
  const d = ts.toDate ? ts.toDate() : new Date(ts);
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

export function ChatWindow({
  session, messages, agentUid, visitorTyping,
  macros, onSend, onInput, onEndChat, onTransfer, findMacro,
}: Props) {
  const [input, setInput] = useState('');
  const [isNote, setIsNote] = useState(false);
  const [macroSuggestion, setMacroSuggestion] = useState<Macro | null>(null);
  const messagesEnd = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLTextAreaElement>(null);

  useEffect(() => {
    messagesEnd.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  if (!session) {
    return (
      <div className="ac-chat-window ac-chat-window--empty">
        <div className="ac-chat-empty">
          <h3>Select a conversation</h3>
          <p>Choose a chat from the sidebar to start responding.</p>
        </div>
      </div>
    );
  }

  const handleInputChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    const val = e.target.value;
    setInput(val);
    onInput();

    // Check for macro shortcut.
    const macro = findMacro(val);
    setMacroSuggestion(macro || null);
  };

  const handleSend = () => {
    if (!input.trim()) return;

    // Check if it's a macro shortcut.
    if (macroSuggestion) {
      onSend(macroSuggestion.text, isNote);
      setInput('');
      setMacroSuggestion(null);
      return;
    }

    onSend(input, isNote);
    setInput('');
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
    // Tab to accept macro suggestion.
    if (e.key === 'Tab' && macroSuggestion) {
      e.preventDefault();
      setInput(macroSuggestion.text);
      setMacroSuggestion(null);
    }
  };

  return (
    <div className="ac-chat-window">
      {/* Header */}
      <div className="ac-chat-header">
        <div className="ac-chat-header__info">
          <strong>{session.visitorName || 'Visitor'}</strong>
          <span className="ac-chat-header__email">{session.visitorEmail}</span>
        </div>
        <div className="ac-chat-header__actions">
          <button className="ac-btn ac-btn--small" onClick={onTransfer} title="Transfer chat">
            Transfer
          </button>
          <button className="ac-btn ac-btn--small ac-btn--danger" onClick={onEndChat} title="End chat">
            End
          </button>
        </div>
      </div>

      {/* Messages */}
      <div className="ac-chat-messages">
        {messages.map(msg => (
          <div
            key={msg.id}
            className={`ac-chat-msg ${
              msg.senderType === 'agent' ? 'ac-chat-msg--agent' :
              msg.senderType === 'system' || msg.isNote ? 'ac-chat-msg--system' :
              'ac-chat-msg--visitor'
            }`}
          >
            {msg.isNote && <span className="ac-chat-msg__note-badge">Note</span>}
            <div className="ac-chat-msg__text">{msg.text}</div>
            <div className="ac-chat-msg__meta">
              <span>{formatTime(msg.timestamp)}</span>
              {msg.senderType === 'agent' && (
                <span className="ac-chat-msg__read">
                  {msg.readByVisitor ? '✓✓' : '✓'}
                </span>
              )}
            </div>
          </div>
        ))}
        {visitorTyping && (
          <div className="ac-chat-msg ac-chat-msg--typing">
            <span className="ac-typing-dots">
              <span /><span /><span />
            </span>
          </div>
        )}
        <div ref={messagesEnd} />
      </div>

      {/* Input */}
      <div className="ac-chat-input">
        <div className="ac-chat-input__toolbar">
          <label className="ac-chat-input__note-toggle">
            <input
              type="checkbox"
              checked={isNote}
              onChange={(e) => setIsNote(e.target.checked)}
            />
            <span>Internal note</span>
          </label>
        </div>
        {macroSuggestion && (
          <div className="ac-macro-suggestion">
            <strong>/{macroSuggestion.shortcut}</strong>: {macroSuggestion.title}
            <span className="ac-macro-hint">Tab to insert</span>
          </div>
        )}
        <div className="ac-chat-input__row">
          <textarea
            ref={inputRef}
            className={`ac-chat-input__text ${isNote ? 'ac-chat-input__text--note' : ''}`}
            value={input}
            onChange={handleInputChange}
            onKeyDown={handleKeyDown}
            placeholder={isNote ? 'Add an internal note…' : 'Type a message…'}
            rows={1}
          />
          <button className="ac-chat-input__send" onClick={handleSend} disabled={!input.trim()}>
            Send
          </button>
        </div>
      </div>
    </div>
  );
}
