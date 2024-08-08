<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="box box-solid">

    <div style="height: 417px;">

        @php

                    $gift=DB::table('gifts')->where('type',5)->where('enable',true)->get();
                   // $users=DB::table('users')->get();
        @endphp
        <form method="POST" action="{{ route('admin.postAddGiftAchievement') }}" class="formcustumPage" enctype="multipart/form-data"
        >
            @csrf


                <div class="form-group">
                 <label for="user">المستخدمين</label>
                    <select name="user_id" id="user_id" class="form-control select2" required>
                        <option value="">Select User</option>
                    </select>
                </div>
                <input type="hidden" name="achievement_id"  value="{{ $achievement_id }}" class="inputs_cus_form">
                <div class="form-group">
                    <label for="currentVersion" class="control-label">الهدايا</label>
                    <select name="gift_id" id="cars" class="inputs_cus_form" required>
                        <option value="">{{__('select Gift')}}</option>
                        @foreach ($gift as $label)
                            <option value="{{ $label->id }}">{{ $label->name }}</option>
                        @endforeach
                    </select>
                </div>

            <button type="submit" class="button_form_cus">Submit</button>
        </form>
        <br>

    </div>
    <!-- /.box-body -->
    @if(session()->has('message'))
    <div class="alert alert-danger form-group">
        {{ session()->get('message') }}
    </div>
@endif

<script>

      $(document).ready(function() {

        $('#user_id').select2({
            ajax: {
                url: '{{ route('search.users') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    var query = {
                        search: params.term,
                        type: 'public',
                        page: params.page || 1
                    };

                    return query;
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    return {
                        results: data.data.map(function(user) {
                            return { id: user.id, text: user.name };
                        }),
                        pagination: {
                            more: (params.page * 10) < data.total
                        }
                    };
                },
                cache: true
            },
          placeholder: 'Search users',
        });


  });

</script>

</div>


