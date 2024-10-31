<form id="paymentForm" onsubmit="event.preventDefault(); return extracted()">
    @csrf
    <div class="form-group">
        <label for="amount">Amount</label>
        <input type="number" class="form-control" id="amount" name="amount" required>
    </div>
    <div class="form-group">
        <label for="type">type</label>
        <select name="type" id="type" class="form-control">
            <option value="game_type">game type</option>
        </select>
    </div>

    <button type="submit" id="submitButton" class="btn btn-primary">Save</button>
</form>

<!-- Placeholder for the success message or payment URL -->
<div id="responseMessage"></div>