import { Component } from '@angular/core';
import { Observable, of, BehaviorSubject } from 'rxjs';
import { map, switchMap, tap, startWith } from 'rxjs/operators';
import { SettingsResolver } from './service/settings-resolver';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  title = 'easyprice';
  private model: Observable<{ id: string }>;
  private subscriber: any;
  private settings: BehaviorSubject<{}>;

  constructor(private settingsResolver: SettingsResolver) {
    this.model = new Observable<{ id: string }>(subscriber => this.subscriber = subscriber);

    this.settings = new BehaviorSubject<{}>({
      'ui_show_logo': 0,
      'ui_show_search_summary_bloc': 0,
      'ui_show_search_details_bloc': 0
    });

    this.settingsResolver.resolve({}).subscribe(settings => this.settings.next(settings));

    /* this.settings = of({
      'ui_show_logo': 0,
      'ui_show_search_summary_bloc': 0,
      'ui_show_search_details_bloc': 0
    }).pipe(switchMap(settings => this.settingsResolver.resolve({})), tap(settings => console.log(settings))); */
    /* this.settings = this.settingsResolver.resolve({}).pipe(tap(settings => console.log(settings))); */
  }

  ngOnInit() {
  }

  updateModel = (model: Observable<{ id: string }>) => model.subscribe(model => {
    this.settingsResolver.resolveFromModel(model, {}).subscribe(settings => { this.settings.next(settings); this.subscriber.next(model); });
  });
}
