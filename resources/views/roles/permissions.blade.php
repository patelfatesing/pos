{{-- resources/views/admin/roles/permissions_ui.blade.php --}}
@extends('layouts.backend.layouts')

@section('styles')
<style>
    :root{
        --ui-border:#e5e7eb;
        --ui-muted:#6b7280;
        --ui-bg:#f9fafb;
        --ui-head:#111827;
        --ui-accent:#0d6efd; /* Bootstrap primary */
        --ui-soft:#f3f4f6;
    }

    .card.ui-role {
        border: 1px solid var(--ui-border);
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(16,24,40,.04);
        overflow: hidden;
    }

    .ui-card-head {
        background: #fff;
        padding: 14px 18px;
        border-bottom: 1px solid var(--ui-border);
    }
    .ui-title {
        font-weight: 700;
        color: var(--ui-head);
        margin: 0;
    }
    .ui-sub {
        color: var(--ui-muted);
        font-size: .925rem;
    }

    /* Accordion */
    .perm-accordion .accordion-item {
        border: 1px solid var(--ui-border);
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }
    .perm-accordion .accordion-item + .accordion-item { margin-top: 12px; }
    .perm-accordion .accordion-button {
        background: var(--ui-bg);
        font-weight: 600;
        color: var(--ui-head);
        padding: .9rem 1rem;
        gap: .75rem;
        box-shadow: none !important;
        border: 0;
    }
    .perm-accordion .accordion-button:not(.collapsed) { background: #eef3ff; }
    .perm-accordion .accordion-button:focus { box-shadow: 0 0 0 .2rem rgba(13,110,253,.15) !important; }
    .perm-accordion .accordion-button .module-title {
        display: flex; align-items: center; gap:.6rem;
    }

    /* Enable switch inline */
    .module-switch.form-switch { margin: 0; padding-left: 2.75rem; }
    .module-switch .form-check-input {
        width: 2.4em; height: 1.3em;
    }
    .module-switch .form-check-label {
        font-weight: 600; color: var(--ui-head);
    }
    .module-hint {
        font-size: .84rem; color: var(--ui-muted);
        margin-left: .5rem;
    }

    /* Body */
    .accordion-body {
        padding: 1rem 1rem 1.25rem;
        background: #fff;
    }

    /* Table */
    .perm-table {
        border: 1px solid var(--ui-border);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0;
    }
    .perm-table thead th {
        background: #f1f5f9;
        border-bottom: 1px solid var(--ui-border);
        font-weight: 700;
        color: var(--ui-head);
        padding: .75rem .9rem;
    }
    .perm-table tbody td {
        vertical-align: middle;
        padding: .7rem .9rem;
        border-top: 1px solid var(--ui-border);
        background: #fff;
    }
    .perm-table tbody tr:nth-child(odd) td { background: var(--ui-soft); }
    .w-access { min-width: 220px; }

    /* Selects */
    .form-select.w-auto { min-width: 160px; }
    .form-select:focus { box-shadow: 0 0 0 .2rem rgba(13,110,253,.15); border-color: var(--ui-accent); }
    .is-disabled .form-select { opacity: .6; cursor: not-allowed; }

    /* Footer */
    .ui-footer {
        position: sticky; bottom: 0; z-index: 5;
        background: #fff; border-top: 1px solid var(--ui-border);
        padding: .75rem .9rem; text-align: right;
    }

    /* Tiny badges legend (optional) */
    .ui-legend { font-size: .85rem; color: var(--ui-muted); }
    .ui-legend .badge { font-weight: 600; }
</style>
@endsection

@section('page-content')
<div class="wrapper">
  <div class="content-page">
    <div class="container-fluid">
      <div class="card ui-role">
        <div class="ui-card-head d-flex align-items-center justify-content-between">
          <div>
            <h4 class="ui-title">Role Permissions — {{ $role->name }}</h4>
            <div class="ui-sub">Control module access and action scopes for this role.</div>
          </div>
          <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Back</a>
        </div>

        <form method="POST" action="{{ route('roles.permissions.update', $role) }}">
          @csrf

          <div class="card-body">
            <!-- Legend (optional) -->
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="ui-legend">
                <span class="me-2">Scopes:</span>
                <span class="badge bg-light text-dark border me-1">None</span>
                <span class="badge bg-primary-subtle text-primary border me-1">Own</span>
                <span class="badge bg-primary text-white">All</span>
              </div>
            </div>

            <div class="accordion perm-accordion" id="permAccordion">
              @php
                $has = fn($n) => in_array($n, $current ?? [], true);
                $bin = fn($m,$a) => $has("$m.$a") ? 'yes' : 'no';
                $sc  = function ($m,$a) use($has) {
                    if ($has("$m.$a.all")) return 'all';
                    if ($has("$m.$a.own")) return 'own';
                    return 'none';
                };
                $i=0;
                $openFirst = true; // will open the first item if openKey not set
              @endphp

              @foreach ($modules as $key => $label)
                @php
                  $i++;
                  $open = isset($openKey) ? $openKey === $key : $openFirst;
                  $openFirst = false;
                  $enabled = $bin($key, 'enable') === 'yes';
                  $listLabel = $key === 'users' ? 'User List' : $label . ' List';
                @endphp

                <div class="accordion-item">
                  <h2 class="accordion-header" id="h-{{ $i }}">
                    <button class="accordion-button {{ $open ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#c-{{ $i }}">
                      <div class="form-check form-switch module-switch">
                        <input class="form-check-input module-toggle" type="checkbox"
                               id="chk-{{ $i }}" data-target="en-{{ $i }}"
                               {{ $enabled ? 'checked' : '' }}>
                        <label class="form-check-label" for="chk-{{ $i }}">{{ $label }}</label>
                      </div>
                      <span class="module-hint">{{ $enabled ? 'Enabled' : 'Disabled' }}</span>
                    </button>
                  </h2>

                  <div id="c-{{ $i }}" class="accordion-collapse collapse {{ $open ? 'show' : '' }}"
                       data-bs-parent="#permAccordion">
                    <div class="accordion-body {{ $enabled ? '' : 'is-disabled' }}">
                      {{-- hidden enable field --}}
                      <input type="hidden" id="en-{{ $i }}" name="enable[{{ $key }}]" value="{{ $enabled ? 'yes' : 'no' }}">

                      <div class="table-responsive">
                        <table class="table perm-table">
                          <thead>
                            <tr>
                              <th style="width:50%">Permission</th>
                              <th style="width:50%">Access</th>
                            </tr>
                          </thead>
                          <tbody>
                            {{-- Create: Yes/No --}}
                            @php $vC = $bin($key,'create'); @endphp
                            <tr>
                              <td>Create {{ $label }}</td>
                              <td class="w-access">
                                <select name="create[{{ $key }}]" class="form-select w-auto perm-input" {{ $enabled ? '' : 'disabled' }}>
                                  <option value="yes" {{ $vC=='yes'?'selected':'' }}>Yes</option>
                                  <option value="no"  {{ $vC=='no'?'selected':''  }}>No</option>
                                </select>
                              </td>
                            </tr>

                            {{-- Update: None/Own/All --}}
                            @php $vU = $sc($key,'update'); @endphp
                            <tr>
                              <td>Update {{ $label }}</td>
                              <td class="w-access">
                                <select name="update[{{ $key }}]" class="form-select w-auto perm-input" {{ $enabled ? '' : 'disabled' }}>
                                  <option value="none" {{ $vU=='none'?'selected':'' }}>None</option>
                                  <option value="own"  {{ $vU=='own'?'selected':''  }}>Own</option>
                                  <option value="all"  {{ $vU=='all'?'selected':''  }}>All</option>
                                </select>
                              </td>
                            </tr>

                            {{-- Listing: None/Own/All --}}
                            @php $vL = $sc($key,'listing'); @endphp
                            <tr>
                              <td>{{ $listLabel }}</td>
                              <td class="w-access">
                                <select name="listing[{{ $key }}]" class="form-select w-auto perm-input" {{ $enabled ? '' : 'disabled' }}>
                                  <option value="none" {{ $vL=='none'?'selected':'' }}>None</option>
                                  <option value="own"  {{ $vL=='own'?'selected':''  }}>Own</option>
                                  <option value="all"  {{ $vL=='all'?'selected':''  }}>All</option>
                                </select>
                              </td>
                            </tr>

                            {{-- Delete: None/Own/All --}}
                            @php $vD = $sc($key,'delete'); @endphp
                            <tr>
                              <td>Delete {{ $label }}</td>
                              <td class="w-access">
                                <select name="delete[{{ $key }}]" class="form-select w-auto perm-input" {{ $enabled ? '' : 'disabled' }}>
                                  <option value="none" {{ $vD=='none'?'selected':'' }}>None</option>
                                  <option value="own"  {{ $vD=='own'?'selected':''  }}>Own</option>
                                  <option value="all"  {{ $vD=='all'?'selected':''  }}>All</option>
                                </select>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          <div class="ui-footer">
            <button class="btn btn-primary px-4">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
{{-- If Bootstrap JS is not already loaded in your layout, keep this line. Otherwise you can remove it. --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Toggle enable/disable inner selects + hint text + hidden value
  document.querySelectorAll('.module-toggle').forEach(chk=>{
    chk.addEventListener('change', e=>{
      const header = e.target.closest('.accordion-button');
      const hint   = header?.querySelector('.module-hint');
      const collapse = header?.getAttribute('data-bs-target');
      const body   = collapse ? document.querySelector(collapse+' .accordion-body') : null;
      const hidden = document.getElementById(e.target.dataset.target);

      const enabled = e.target.checked;
      if (hidden) hidden.value = enabled ? 'yes' : 'no';
      if (hint)   hint.textContent = enabled ? 'Enabled' : 'Disabled';
      if (body) {
        body.classList.toggle('is-disabled', !enabled);
        body.querySelectorAll('.perm-input').forEach(el=>{
          el.disabled = !enabled;
        });
      }
    });
  });
</script>
@endsection
