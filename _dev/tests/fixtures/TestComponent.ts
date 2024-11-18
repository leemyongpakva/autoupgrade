import ComponentAbstract from '../../src/ts/components/ComponentAbstract';

export default class TestComponent extends ComponentAbstract {
  public getElement<T extends HTMLElement>(selector: string, errorMessage: string): T {
    return this.queryElement<T>(selector, errorMessage);
  }
}
