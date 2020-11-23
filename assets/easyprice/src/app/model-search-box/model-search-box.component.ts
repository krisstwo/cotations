import { Component, EventEmitter, Input, OnInit, Output, ElementRef, ViewChild } from '@angular/core';
import { Observable, of, Subscriber, Subject, merge } from 'rxjs';
import { catchError, debounceTime, distinctUntilChanged, startWith, switchMap, tap, filter, map } from 'rxjs/operators';
import { Restangular } from 'ngx-restangular';
import { NgbTypeaheadSelectItemEvent, NgbTypeahead } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-model-search-box',
  templateUrl: './model-search-box.component.html',
  styleUrls: ['./model-search-box.component.css']
})
export class ModelSearchBoxComponent implements OnInit {

  private model: any;
  private modelObservable: Observable<{ id: string }>;
  private modelSubscriber: any = false;
  private searching: boolean = false;
  private searchFailed: boolean = false;
  private exclude: boolean = true;
  private lastTerm: string = '';
  private lastResults: [] = [];
  @Input('updateModel') updateModel;
  @ViewChild('input', {static: true}) input: ElementRef;
  @ViewChild('instance', {static: true}) instance: NgbTypeahead;
  private focus$ = new Subject<string>();
  private click$ = new Subject<string>();

  constructor(private restangular: Restangular) {
    this.modelObservable = new Observable<{ id: string }>(subscriber => {
      this.modelSubscriber = subscriber;

      return {
        unsubscribe(): void {
        }
      }
    })
  }

  ngOnInit() {
    this.updateModel(this.modelObservable);
  }

  search = (text$: Observable<string>) =>
    {

    const textSearch$ = text$.pipe(
      debounceTime(500), 
      distinctUntilChanged(),
      tap(() => this.searching = true),
      switchMap(term => {
        this.lastTerm = term;
        return this.restangular.all('search/model-by-needle').getList({ needle: term, exclude: this.exclude }).pipe(
          tap<[]>((results) => {this.searchFailed = false; this.lastResults = results;}),
          catchError(() => {
            this.searchFailed = true;
            this.lastResults = [];
            return of([]);
          }));
      }
      ),
      tap(() => this.searching = false)
      );
    const clicksWithClosedPopup$ = this.click$.pipe(filter(() => !this.instance.isPopupOpen() && !(this.lastResults.length === 0)), map(() => this.lastResults));
    const inputFocus$ = this.focus$.pipe(filter(() => !this.instance.isPopupOpen() && !(this.lastResults.length === 0)), map(() => this.lastResults));

    return merge(textSearch$, inputFocus$, clicksWithClosedPopup$);
  }

  formatter = (x: { id: string, lib_model: string }) => this.lastTerm;

  selectItem = (event: NgbTypeaheadSelectItemEvent) => {
    this.input.nativeElement.blur();

    if (this.modelSubscriber)
      this.modelSubscriber.next(event.item)
  };
}
