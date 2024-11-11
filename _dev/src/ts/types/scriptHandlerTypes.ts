import ComponentAbstract from './ComponentAbstract';

interface RoutesMatching {
  [key: string]: new () => ComponentAbstract;
}

export type { RoutesMatching };
