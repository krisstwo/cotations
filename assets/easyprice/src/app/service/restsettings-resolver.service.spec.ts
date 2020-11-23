import { TestBed } from '@angular/core/testing';

import { RESTSettingsResolverService } from './restsettings-resolver.service';

describe('RESTSettingsResolverService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: RESTSettingsResolverService = TestBed.get(RESTSettingsResolverService);
    expect(service).toBeTruthy();
  });
});
