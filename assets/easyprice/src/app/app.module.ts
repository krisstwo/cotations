import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { AppComponent } from './app.component';
import { ModelSearchBoxComponent } from './model-search-box/model-search-box.component';
import { RestangularModule, Restangular } from 'ngx-restangular';
import { RestangularDefaultConfigFactory } from './app.factories';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { PriceInformationSummaryComponent } from './price-information-summary/price-information-summary.component';
import { SettingsResolver } from './service/settings-resolver';
import { RESTSettingsResolverService } from './service/restsettings-resolver.service';

@NgModule({
  declarations: [
    AppComponent,
    ModelSearchBoxComponent,
    PriceInformationSummaryComponent
  ],
  imports: [
    BrowserModule,
    RestangularModule.forRoot(RestangularDefaultConfigFactory),
    ReactiveFormsModule,
    BrowserAnimationsModule,
    NgbModule,
    FormsModule
  ],
  providers: [
    {provide: SettingsResolver, useClass: RESTSettingsResolverService, deps: [Restangular]}
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
