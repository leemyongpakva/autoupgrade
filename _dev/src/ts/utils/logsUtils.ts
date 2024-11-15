import {
  ErrorSeverity,
  LogEntry,
  LogsSeverity,
  SeverityClasses,
  SuccessSeverity,
  WarningSeverity
} from '../types/logsTypes';

/**
 * @public
 * @type {Record<LogsSeverity, SeverityClasses>}
 * @description Maps severity levels to their corresponding CSS classes for styling and process purposes.
 */
export const severityToClassMap = {
  ...Object.fromEntries(Object.values(SuccessSeverity).map((s) => [s, SeverityClasses.SUCCESS])),
  ...Object.fromEntries(Object.values(WarningSeverity).map((s) => [s, SeverityClasses.WARNING])),
  ...Object.fromEntries(Object.values(ErrorSeverity).map((s) => [s, SeverityClasses.ERROR]))
} as Record<LogsSeverity, SeverityClasses>;

/**
 * @public
 * @param {string} log - The log string to be parsed. Should be in the format: "SEVERITY - Message".
 * @returns {LogEntry} An object containing the severity class and log message.
 * @description Parses a log string to extract its severity and corresponding CSS class for styling.
 */
export function parseLogWithSeverity(log: string): LogEntry {
  const logTrimed = log.trim();
  const regex = /^([A-Z]+)\s*-\s*(.*)$/;
  const match = logTrimed.match(regex);

  if (match) {
    const severityStr = match[1] as LogsSeverity;
    const message = match[2];
    const className = severityToClassMap[severityStr] || SeverityClasses.ERROR;

    return { className, message };
  }

  return { className: SeverityClasses.ERROR, message: log };
}
