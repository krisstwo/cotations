import { Component, Input, OnInit } from '@angular/core';
import { Observable, of } from 'rxjs';
import { distinctUntilChanged, startWith, switchMap, tap, delay } from 'rxjs/operators';
import { Restangular } from 'ngx-restangular';

@Component({
  selector: 'app-price-information-summary',
  templateUrl: './price-information-summary.component.html',
  styleUrls: ['./price-information-summary.component.css']
})
export class PriceInformationSummaryComponent implements OnInit {

  @Input('model') model: Observable<{ id: string }>;
  @Input('settings') settings: Observable<{}>;//TODO: use some app wide store
  private priceInformation: any = { id: null, easyprice: { apa: 0, bpa: 0, cpa: 0, apv: 0, bpv: 0, cpv: 0 } };
  private stockInformation: any;
  private isUpdatingStock: boolean = false;
  private isUpdatingStockError: boolean = false;
  private currentModel: any;

  constructor(private restangular: Restangular) {

  }

  ngOnInit() {
    this.model.subscribe(
      model => this.restangular.all('search/summary-by-model').getList({ model: model.id }).subscribe(data => {
        this.currentModel = model;
        this.priceInformation = data[0];

        // Stock data
        this.stockInformation = null;
        this.isUpdatingStockError = false;
        this.isUpdatingStock = true;
        this.restangular.one('search/model', model.id).one('stock').get().subscribe(stock => {
          this.isUpdatingStock = false;
          this.stockInformation = stock;
        }, () => { this.isUpdatingStock = false; this.isUpdatingStockError = true; }, () => { this.isUpdatingStock = false; this.isUpdatingStock = false });
      })
    );

    // this.priceInformation = this.model.pipe(
    //   startWith({id: null, easyprice: {apa: 0, bpa: 0, cpa: 0, apv: 0, bpv: 0, cpv: 0}}),
    //   distinctUntilChanged(),
    //   switchMap(model => this.restangular.all('search/summary-by-model').getList({model: model.id})),
    //   tap( (data) => console.log(data))
    // );
  }

  refreshStock = ($event) => {
    this.stockInformation = null;
      this.isUpdatingStockError = false;
      this.isUpdatingStock = true;
    this.restangular.one('search/model', this.currentModel.id).one('stock').get()
      .subscribe(stock => {
        this.isUpdatingStock = false;
        this.stockInformation = stock;
      }, () => { this.isUpdatingStock = false; this.isUpdatingStockError = true; }, () => { this.isUpdatingStock = false; this.isUpdatingStock = false });
  }

}
