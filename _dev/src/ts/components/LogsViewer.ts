import { ComponentAbstract } from './ComponentAbstract';
import {
  ErrorSeverity,
  LogEntry,
  LogSaved,
  LogsSeverity,
  SeverityClasses,
  SuccessSeverity,
  WarningSeverity
} from '../types/logsTypes';

export class LogsViewer extends ComponentAbstract {
  #warning: LogSaved[] = [];
  #errors: LogSaved[] = [];

  get #logsList(): HTMLDivElement {
    const logsList = this.element.querySelector('[data-slot-component="list"]') as HTMLDivElement;
    if (!logsList) throw new Error('Logs list not found');
    return logsList;
  }

  get #logsScroll(): HTMLDivElement {
    const logsScroll = this.element.querySelector(
      '[data-slot-component="scroll"]'
    ) as HTMLDivElement;
    if (!logsScroll) throw new Error('Logs list not found');
    return logsScroll;
  }

  get #logsSummary(): HTMLDivElement {
    const logsSummary = this.element.querySelector(
      '[data-slot-component="summary"]'
    ) as HTMLDivElement;
    if (!logsSummary) throw new Error('Logs summary not found');
    return logsSummary;
  }

  #getTemplateLogLine(): HTMLTemplateElement {
    const templateLogLine = document.getElementById('log-line') as HTMLTemplateElement;
    if (!templateLogLine) throw new Error('Template log line not found');
    return templateLogLine;
  }

  public addLogs = (logs: string[]): void => {
    const templateLogLine = this.#getTemplateLogLine();

    const fragment = document.createDocumentFragment();

    logs.forEach((log) => {
      const logLineFragment = templateLogLine.content.cloneNode(true) as DocumentFragment;
      const logLine = logLineFragment.querySelector('.logs__line') as HTMLDivElement;

      const logEntry = this.#parseLogWithSeverity(log);

      logLine.classList.add(`logs__line--${logEntry.className}`);
      logLine.setAttribute('data-status', logEntry.className);
      let id: string | null = null;

      if (logEntry.className === SeverityClasses.WARNING) {
        id = `warning-${this.#warning.length}`;
        this.#warning.push({
          message: logEntry.message,
          id: id
        });
      }

      if (logEntry.className === SeverityClasses.ERROR) {
        id = `error-${this.#errors.length}`;
        this.#errors.push({
          message: logEntry.message,
          id: id
        });
      }

      if (id !== null) {
        logLine.id = id;
      }

      logLine.textContent = logEntry.message;

      fragment.appendChild(logLine);
    });

    this.#logsList.appendChild(fragment);

    this.#logsScroll.scrollTop = this.#logsScroll.scrollHeight;
  };

  #parseLogWithSeverity = (log: string): LogEntry => {
    const regex = /^([A-Z]+)\s*-\s*(.*)$/;
    const match = log.match(regex);

    if (match) {
      const severityStr = match[1] as LogsSeverity;
      const message = match[2];

      let className: SeverityClasses;

      switch (severityStr) {
        case SuccessSeverity.DEBUG:
        case SuccessSeverity.INFO:
        case SuccessSeverity.NOTICE:
          className = SeverityClasses.SUCCESS;
          break;
        case WarningSeverity.WARNING:
          className = SeverityClasses.WARNING;
          break;
        case ErrorSeverity.ERROR:
        case ErrorSeverity.CRITICAL:
        case ErrorSeverity.ALERT:
        case ErrorSeverity.EMERGENCY:
          className = SeverityClasses.ERROR;
          break;
        default:
          throw new Error(`Unknown severity: ${severityStr}`);
      }

      return { className, message };
    }

    return {
      className: SeverityClasses.ERROR,
      message: log
    };
  };
}
