import type { Listener } from '../types/Store';

export default abstract class StoreAbstract<T> {
  private listeners: Listener<T>[] = [];

  subscribe(listener: Listener<T>): () => void {
    this.listeners.push(listener);

    return () => {
      this.listeners = this.listeners.filter((l) => l !== listener);
    };
  }

  protected notify(state: T): void {
    this.listeners.forEach((listener) => listener(state));
  }
}
