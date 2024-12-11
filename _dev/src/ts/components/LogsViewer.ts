import ComponentAbstract from './ComponentAbstract';
import { LogEntry, Log } from '../types/logsTypes';
import { parseLogWithSeverity, debounce } from '../utils/logsUtils';
import DomLifecycle from '../types/DomLifecycle';
import { logStore } from '../store/LogStore';

export default class LogsViewer extends ComponentAbstract implements DomLifecycle {
  #logsIndexHeight: Map<number, number> = new Map();
  #logsListHeight: number = this.#logsList.clientHeight;
  #bufferSize = 1;

  get #logsScroll() {
    return this.queryElement<HTMLDivElement>(
      '[data-slot-component="scroll"]',
      'Logs scroll not found'
    );
  }

  get #logsList() {
    return this.queryElement<HTMLDivElement>('[data-slot-component="list"]', 'Logs list not found');
  }

  #templateLogLine = this.queryElement<HTMLTemplateElement>(
    '#log-line',
    'Template log line not found'
  );

  public mount = () => {
    this.#logsScroll.addEventListener('scroll', this.#debouncedRefreshView);
  };

  public beforeDestroy = () => {
    this.#logsScroll.removeEventListener('scroll', this.#debouncedRefreshView);
  };

  public addLogs = (logs: string[]): void => {
    let count = 0;

    logs.forEach((log) => {
      const id = logStore.getLogs().length;
      const logEntry = parseLogWithSeverity(log);
      const HTMLElement = this.#createLogLine(logEntry);
      this.#logsList.appendChild(HTMLElement);

      const height = HTMLElement.offsetHeight;
      const offsetTop = HTMLElement.offsetTop;

      logStore.addLog({
        ...logEntry,
        height,
        offsetTop,
        HTMLElement
      });

      this.#logsIndexHeight.set(id, offsetTop);
      this.#logsListHeight += height;

      count += 1;

      if (count > 20) {
        this.#scrollToBottom();
        count = 0;
      }
    });

    this.#scrollToBottom();
  };

  #createLogLine = (logEntry: LogEntry): HTMLDivElement => {
    const logLineFragment = this.#templateLogLine.content.cloneNode(true) as DocumentFragment;
    const logLine = logLineFragment.querySelector('.logs__line') as HTMLDivElement;

    logLine.classList.add(`logs__line--${logEntry.severity}`);
    logLine.setAttribute('data-status', logEntry.severity);
    logLine.textContent = logEntry.message;

    return logLine;
  };

  #scrollToBottom = () => {
    this.#logsScroll.scrollTop = this.#logsListHeight;
    this.refreshView();
  };

  refreshView = () => {
    const scrollTop = this.#logsScroll.scrollTop;
    const viewportHeight = this.#logsScroll.clientHeight;

    // calc vision limit with buffer
    const startBoundary = scrollTop - this.#bufferSize * viewportHeight;
    const endBoundary = scrollTop + viewportHeight + this.#bufferSize * viewportHeight;

    // search index of visible logs
    const visibleLogs: Log[] = [];
    let marginTop = 0;
    let marginBottom = 0;

    for (const [id, offsetTop] of this.#logsIndexHeight.entries()) {
      const log = logStore.getLogs()[id];
      const logHeight = log.height;

      if (offsetTop + logHeight < startBoundary) {
        marginTop += logHeight;
      } else if (offsetTop > endBoundary) {
        marginBottom += logHeight;
      } else {
        visibleLogs.push(log);
      }
    }

    this.#logsList.style.marginTop = `${marginTop}px`;
    this.#logsList.style.marginBottom = `${marginBottom}px`;

    this.#logsList.innerHTML = '';
    visibleLogs.forEach((log) => {
      if (log.HTMLElement) {
        this.#logsList.appendChild(log.HTMLElement);
      }
    });
  };

  #debouncedRefreshView = debounce(() => {
    this.refreshView();
  }, 200);
}
