import DialogContainer from './components/DialogContainer';
import RouteHandler from './routing/RouteHandler';
import ScriptHandler from './routing/ScriptHandler';

export const routeHandler = new RouteHandler();

export const dialogContainer = new DialogContainer();
export const scriptHandler = new ScriptHandler();

export default { routeHandler, scriptHandler, dialogContainer };
