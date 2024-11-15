import { ComponentAbstract } from './ComponentAbstract';

export class LogsSummary extends ComponentAbstract {
  #logsSummaryText = this.queryElement<HTMLDivElement>(
    '[data-slot-component="text"]',
    'Logs summary text not found'
  );

  /**
   * @public
   * @param text - text summary to display.
   * @description Allows to update the summary text of the logs.
   */
  public setLogsSummaryText = (text: string): void => {
    this.#logsSummaryText.innerText = text;
  };
}
