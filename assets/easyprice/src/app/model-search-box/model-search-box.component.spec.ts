import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ModelSearchBoxComponent } from './model-search-box.component';

describe('ModelSearchBoxComponent', () => {
  let component: ModelSearchBoxComponent;
  let fixture: ComponentFixture<ModelSearchBoxComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ModelSearchBoxComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ModelSearchBoxComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
