import { Observable } from 'rxjs';

export abstract class SettingsResolver {
    abstract resolve(settings: {}): Observable<{}>;
    abstract resolveFromModel(model: {}, settings: {}): Observable<{}>;
}
