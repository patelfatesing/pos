@extends('layouts.backend.layouts')

@section('page-content')
<div class="wrapper">
  <div class="content-page">
    <div class="container-fluid add-form-list">
      <div class="row">
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between">
              <div class="header-title"><h4 class="card-title">Edit Ledger</h4></div>
              <div><a href="{{ route('accounting.ledgers.index') }}" class="btn btn-secondary">Back</a></div>
            </div>

            <div class="card-body">
              @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

              <form action="{{ route('accounting.ledgers.update',$ledger->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Ledger Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name',$ledger->name) }}" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Group</label>
                    <select name="group_id" class="form-control" required>
                      @foreach($groups as $g)
                        <option value="{{ $g->id }}" @selected(old('group_id',$ledger->group_id)==$g->id)>{{ $g->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-control">
                      <option value="">All / None</option>
                      @foreach(($branches ?? []) as $b)
                        <option value="{{ $b->id }}" @selected(old('branch_id',$ledger->branch_id)==$b->id)>{{ $b->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Opening Balance</label>
                    <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance',$ledger->opening_balance) }}">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="opening_type" class="form-control">
                      <option value="Dr" @selected(old('opening_type',$ledger->opening_type)==='Dr')>Dr</option>
                      <option value="Cr" @selected(old('opening_type',$ledger->opening_type)==='Cr')>Cr</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label d-block">Active</label>
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active',$ledger->is_active)?'checked':'' }}> Yes
                  </div>
                </div>

                <div class="mt-3">
                  <button class="btn btn-primary">Update Ledger</button>
                  <button type="reset" class="btn btn-danger">Reset</button>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
