import { Injectable } from '@angular/core';
import { SettingsResolver } from './settings-resolver';
import { Observable } from 'rxjs';
import { Restangular } from 'ngx-restangular';
import { map } from 'rxjs/operators';
import { isNullOrUndefined } from 'util';

@Injectable()
export class RESTSettingsResolverService extends SettingsResolver {

  constructor(private restangular: Restangular) {
    super();
  }

  resolve = (settings: {}): Observable<{}> =>
    this.restangular.one('form/settings').get().pipe(map(data => {
      let resolvedSettings = {};
      for (let key in (<Restangular>data).plain()) {
        if (Object.keys(settings).length === 0 || settings.hasOwnProperty(key))
          resolvedSettings[key] = parseInt(data[key]);
      }

      return resolvedSettings;
    }));

  resolveFromModel = (model: {id_sfam: string, id_structure: string}, settings: {}): Observable<{}> =>
    this.restangular.one('form/settings').get({
      'family': isNullOrUndefined(model.id_sfam) ? '' : model.id_sfam,
      'structure': isNullOrUndefined(model.id_structure) ? '' : model.id_structure
    }).pipe(map(data => {
      let resolvedSettings = {};
      for (let key in (<Restangular>data).plain()) {
        if (Object.keys(settings).length === 0 || settings.hasOwnProperty(key))
          resolvedSettings[key] = parseInt(data[key]);
      }

      return resolvedSettings;
    }));
}
