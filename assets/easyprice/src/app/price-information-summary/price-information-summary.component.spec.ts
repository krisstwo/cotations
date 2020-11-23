import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PriceInformationSummaryComponent } from './price-information-summary.component';

describe('PriceInformationSummaryComponent', () => {
  let component: PriceInformationSummaryComponent;
  let fixture: ComponentFixture<PriceInformationSummaryComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PriceInformationSummaryComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PriceInformationSummaryComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
