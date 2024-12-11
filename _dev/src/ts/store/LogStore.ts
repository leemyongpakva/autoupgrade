import { Log, Severity } from '../types/logsTypes';
import StoreAbastract from './StoreAbstract';

class LogStore extends StoreAbastract<Log[]> {
  #logs: Log[] = [];

  addLog(log: Log): void {
    this.#logs.push(log);
    this.notify(this.#logs);
  }

  getLogs(): Log[] {
    return this.#logs;
  }

  getWarnings(): Log[] {
    return this.#logs.filter((log) => log.severity === Severity.WARNING);
  }

  getErrors(): Log[] {
    return this.#logs.filter((log) => log.severity === Severity.ERROR);
  }

  clearLogs(): void {
    this.#logs = [];
    this.notify(this.#logs);
  }
}

export const logStore = new LogStore();
