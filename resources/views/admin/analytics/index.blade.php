<h2>Google Analytics Dashboard</h2>

@if(!empty($debug))
    <pre style="background:#f5f5f5;padding:10px;max-width:900px;overflow:auto;">
{{ json_encode($debug, JSON_PRETTY_PRINT) }}
    </pre>
@endif

@if(!empty($accessibleProperties))
    <h3>Accessible GA4 Properties (from connected Google account)</h3>
    <pre style="background:#f5f5f5;padding:10px;max-width:900px;overflow:auto;">
{{ json_encode($accessibleProperties, JSON_PRETTY_PRINT) }}
    </pre>
@endif

@if(!empty($error))
    <p style="color: #b91c1c;">
        {{ $error }}
    </p>

    <p>
        <a href="{{ route('admin.google.analytics.connect') }}">Connect Google Analytics</a>
    </p>
@elseif(!empty($analytics))
    @foreach($analytics->getRows() as $row)

        <p>
            Active Users:
            {{ $row->getMetricValues()[0]->getValue() }}
        </p>

    @endforeach
@else
    <p>
        No analytics data yet.
    </p>
@endif
