<section>
    <header class="mb-3">
        <h4>Profile Information</h4>
        <p class="text-muted">Update your account's profile information and email address.</p>
    </header>

    {{-- Send verification form --}}
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- Profile update form --}}
    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="row">
            <!-- Name -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           class="form-control"
                           placeholder="Enter Name"
                           required autofocus>

                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Email -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           class="form-control"
                           placeholder="Enter Email"
                           required>

                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <p class="text-warning mt-2">
                            Your email address is unverified.
                            <button form="send-verification" class="btn btn-link p-0">
                                Click here to re-send the verification email.
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="text-success mt-2">
                                A new verification link has been sent to your email address.
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <button type="submit" class="btn btn-primary mr-2">Save</button>
        <button type="reset" class="btn btn-danger">Reset</button>

        @if (session('status') === 'profile-updated')
            <span class="text-success ml-2">Saved.</span>
        @endif
    </form>
</section>
