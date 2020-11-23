import { Injectable } from '@angular/core';
import { SettingsResolver } from './settings-resolver';
import { Observable } from 'rxjs';

@Injectable()
export class WindowGlobalSettingsResolverService extends SettingsResolver {
  private settings: {} = {};

  constructor() {
    super();
    
    if (window['easyprice'] === undefined) {
      console.warn('window.easyprice is undefined, app is running without settings');
    } else {
      for (let key in window['easyprice'].options) {
        this.settings[key] = parseInt(window['easyprice'].options[key]);
      }
    }
  }

  resolveFromModel(model: {}, settings: {}): Observable<{}> {
    throw new Error("Method not implemented.");
  }

  resolve(settings: {}): Observable<{}> {
    let observable = new Observable(subscriber => {
      let resolvedSettings = {};

      for (let key in settings) {
        if (this.settings[key] !== undefined && ! isNaN(this.settings[key])) {
          resolvedSettings[key] = this.settings[key];
        } else {
          resolvedSettings[key] = settings[key]; // Fallback to passed value
        }
      }

      subscriber.next(resolvedSettings);
    });
    

    return observable;
  }
}
