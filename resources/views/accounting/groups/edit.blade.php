@extends('layouts.backend.layouts')

@section('page-content')
<div class="wrapper">
  <div class="content-page">
    <div class="container-fluid add-form-list">
      <div class="row">
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between">
              <div class="header-title"><h4 class="card-title">Edit Account Group</h4></div>
              <div><a href="{{ route('accounting.groups.list') }}" class="btn btn-secondary">Back</a></div>
            </div>

            <div class="card-body">
              @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
              <form action="{{ route('accounting.groups.update',$group->id) }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="id" value="{{ $group->id }}">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name',$group->name) }}" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Code (optional)</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code',$group->code) }}">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Nature</label>
                    <select name="nature" class="form-control" required>
                      @foreach(['Asset','Liability','Income','Expense'] as $n)
                        <option value="{{ $n }}" @selected(old('nature',$group->nature)===$n)>{{ $n }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Parent Group</label>
                    <select name="parent_id" class="form-control">
                      <option value="">— None —</option>
                      @foreach($parents as $p)
                        <option value="{{ $p->id }}" @selected(old('parent_id',$group->parent_id)==$p->id)>{{ $p->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Affects Gross (P&L)</label><br>
                    <input type="checkbox" name="affects_gross" value="1" {{ old('affects_gross',$group->affects_gross)?'checked':'' }}> Yes
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order',$group->sort_order) }}">
                  </div>
                </div>

                <div class="mt-3">
                  <button class="btn btn-primary">Update Group</button>
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
