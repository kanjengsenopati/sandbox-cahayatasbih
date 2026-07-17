@if(!auth()->user()->outlet_id && request('mode') === 'outlet')
    @php
        $user = auth()->user();
        $authOutletIds = $user->getOutletIds();
        $query = \App\Models\Outlet::where('is_active', 1);

        if (!empty($authOutletIds)) {
            $query->whereIn('id', $authOutletIds);
        } elseif (!$user->hasRole('Super Admin')) {
            $query->where('id', $user->outlet_id);
        }

        $outlets = $query->orderBy('name')->get();
        $selectedOutletId = request('outlet_id');
        if (!$selectedOutletId && $outlets->isNotEmpty()) {
            $selectedOutletId = $outlets->first()->id;
        }
    @endphp
    @if($outlets->isNotEmpty())
        <div class="d-flex align-items-center me-4">
            <span class="fs-7 fw-bold text-gray-700 me-2">Outlet:</span>
            <select id="global_outlet_switcher" class="form-select form-select-solid form-select-sm w-200px" onchange="switchOutlet(this.value)">
                @foreach($outlets as $ot)
                    <option value="{{ $ot->id }}" {{ $selectedOutletId == $ot->id ? 'selected' : '' }}>
                        {{ $ot->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <script>
            function switchOutlet(id) {
                const url = new URL(window.location.href);
                url.searchParams.set('outlet_id', id);
                window.location.href = url.toString();
            }
        </script>
    @endif
@endif
