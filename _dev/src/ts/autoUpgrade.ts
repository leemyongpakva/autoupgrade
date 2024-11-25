import ModalContainer from './components/ModalContainer';
import RouteHandler from './routing/RouteHandler';
import ScriptHandler from './routing/ScriptHandler';

export const routeHandler = new RouteHandler();

export const modalContainer = new ModalContainer();
export const scriptHandler = new ScriptHandler();

export default { routeHandler, scriptHandler, modalContainer };
