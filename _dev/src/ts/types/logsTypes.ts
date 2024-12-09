export enum SuccessSeverity {
  DEBUG = 'DEBUG',
  INFO = 'INFO',
  NOTICE = 'NOTICE'
}

export enum WarningSeverity {
  WARNING = 'WARNING'
}

export enum ErrorSeverity {
  ERROR = 'ERROR',
  CRITICAL = 'CRITICAL',
  ALERT = 'ALERT',
  EMERGENCY = 'EMERGENCY'
}

export enum SeverityClasses {
  SUCCESS = 'success',
  WARNING = 'warning',
  ERROR = 'error'
}

export interface LogEntry {
  severity: SeverityClasses;
  message: string;
}

export interface Log extends LogEntry {
  height: number;
  offsetTop: number;
  HTMLElement?: HTMLDivElement;
}

export type LogsSeverity = SuccessSeverity | WarningSeverity | ErrorSeverity;
