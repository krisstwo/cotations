import { TestBed } from '@angular/core/testing';

import { WindowGlobalSettingsResolverService } from './window-global-settings-resolver.service';

describe('WindowGlobalSettingsResolverService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: WindowGlobalSettingsResolverService = TestBed.get(WindowGlobalSettingsResolverService);
    expect(service).toBeTruthy();
  });
});
