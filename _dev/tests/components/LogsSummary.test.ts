import LogsSummary from '../../src/ts/components/LogsSummary';

describe('LogsSummary', () => {
  let container: HTMLElement;
  let logsSummary: LogsSummary;

  beforeEach(() => {
    document.body.innerHTML = `
    <div data-component="logs-summary" class="log-progress__status">
      <div class="log-progress__icon">
        <i class="material-icons">loop</i>
      </div>
      <div data-slot-component="text" class="log-progress__text"></div>
    </div>
  `;
    container = document.querySelector('[data-component="logs-summary"]')!;
    logsSummary = new LogsSummary(container);
  });

  afterEach(() => {
    document.body.removeChild(container);
  });

  describe('setLogsSummaryText', () => {
    it('should update the logs summary text when setLogsSummaryText is called', () => {
      const newText = 'Processing logs...';
      logsSummary.setLogsSummaryText(newText);

      const textElement = container.querySelector('[data-slot-component="text"]') as HTMLDivElement;
      expect(textElement!.innerText).toBe(newText);
    });

    it('should update the logs summary text to an empty string if provided', () => {
      logsSummary.setLogsSummaryText('');

      const textElement = container.querySelector('[data-slot-component="text"]') as HTMLDivElement;
      expect(textElement!.innerText).toBe('');
    });

    it('should correctly set text containing special characters', () => {
      const specialText = 'Logs: <completed> ✅';
      logsSummary.setLogsSummaryText(specialText);

      const textElement = container.querySelector('[data-slot-component="text"]') as HTMLDivElement;
      expect(textElement!.innerText).toBe(specialText);
    });
  });
});
