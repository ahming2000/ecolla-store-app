<div class="pill-container shadow">
    <div class="pill-container-type-{{ $type }} p-2 mt-1">
        @foreach($elements as $element)
            <span class="badge rounded-pill mb-1 me-1" onclick="openEditElementModal(event)"
                  data-element="{{ $element->toJson() }}" id="{{ $type }}-{{ $element->id }}">
                <i class="bi bi-pencil-square"></i>
                <span class="element-name">{{ $element->name }}</span>
            </span>
        @endforeach
    </div>
</div>
